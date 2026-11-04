import os
import json
import traceback
from pathlib import Path
from datetime import datetime, timedelta

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC


BASE_URL = os.getenv(
    "SKILLMATCH_BASE_URL",
    "http://127.0.0.1:8000"
)

BASE_DIR = Path(__file__).resolve().parents[1]
SCREENSHOTS = BASE_DIR / "screenshots"
REPORTS = BASE_DIR / "reports"

SCREENSHOTS.mkdir(parents=True, exist_ok=True)
REPORTS.mkdir(parents=True, exist_ok=True)


def run():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)
    wait = WebDriverWait(driver, 20)

    result = {
        "suite": "E2E-03 Event Management & Publication",
        "status": "FAIL",
        "steps": []
    }

    def passed(test_id, description):
        result["steps"].append({
            "id": test_id,
            "description": description,
            "status": "PASS"
        })
        print(f"PASS: {test_id} {description}")

    try:
        email = os.getenv("SKILLMATCH_ORGANIZER_EMAIL")
        password = os.getenv("SKILLMATCH_ORGANIZER_PASSWORD")

        if not email or not password:
            raise RuntimeError(
                "Environment variable Organizer belum diatur."
            )

        # TC-01 Login Organizer
        driver.get(f"{BASE_URL}/login")

        wait.until(
            EC.visibility_of_element_located((By.NAME, "email"))
        ).send_keys(email)

        driver.find_element(
            By.NAME, "password"
        ).send_keys(password)

        driver.find_element(
            By.CSS_SELECTOR,
            "form[action*='login'] button[type='submit']"
        ).click()

        wait.until(lambda d: "/login" not in d.current_url)
        passed("TC-01", "Login Organizer")

        # TC-02 Halaman Event Saya
        driver.get(f"{BASE_URL}/organizer/events")

        wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, "//h1[contains(., 'Event Saya')]")
            )
        )

        passed("TC-02", "Halaman Event Saya")

        # TC-03 Membuka Form Buat Event
        button = wait.until(
            EC.presence_of_element_located(
                (By.XPATH, "//button[contains(normalize-space(.), 'Buat Event')]")
            )
        )

        driver.execute_script(
            "arguments[0].click();", button
        )

        wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, "//h2[contains(normalize-space(.), 'Buat Event')]")
            )
        )

        passed("TC-03", "Form Buat Event")

        # TC-04 Memeriksa Pilihan Paket
        package = wait.until(
            EC.presence_of_element_located(
                (By.NAME, "package_id")
            )
        )

        package_options = [
            option.get_attribute("value")
            for option in Select(package).options
        ]

        if "3" not in package_options:
            raise AssertionError("Paket Standard tidak ditemukan.")

        passed("TC-04", "Pilihan paket event")

        # TC-05 Memeriksa Pilihan Kota dan Kategori
        city = driver.find_element(By.NAME, "city_id")
        category = driver.find_element(By.NAME, "category_id")

        city_options = [
            option.get_attribute("value")
            for option in Select(city).options
        ]

        category_options = [
            option.get_attribute("value")
            for option in Select(category).options
        ]

        if "1" not in city_options:
            raise AssertionError("Kota Malang tidak ditemukan.")

        if "4" not in category_options:
            raise AssertionError(
                "Kategori Kegiatan Sosial & Komunitas tidak ditemukan."
            )

        passed("TC-05", "Pilihan kota dan kategori")

        # TC-06 Mengisi Form Event
        now = datetime.now().replace(second=0, microsecond=0)

        data = {
            "package_id": "3",
            "city_id": "1",
            "category_id": "4",
            "title": f"E2E Event {now:%Y%m%d%H%M%S}",
            "description": "Event untuk pengujian otomatis Selenium E2E.",
            "location": "Gedung Pengujian SkillMatch, Malang",
            "registration_opens_at": (
                now + timedelta(days=1)
            ).strftime("%Y-%m-%dT%H:%M"),
            "registration_deadline": (
                now + timedelta(days=3)
            ).strftime("%Y-%m-%dT%H:%M"),
            "assessment_deadline": (
                now + timedelta(days=4)
            ).strftime("%Y-%m-%dT%H:%M"),
            "decision_deadline": (
                now + timedelta(days=5)
            ).strftime("%Y-%m-%dT%H:%M"),
            "starts_at": (
                now + timedelta(days=6)
            ).strftime("%Y-%m-%dT%H:%M"),
            "ends_at": (
                now + timedelta(days=6, hours=8)
            ).strftime("%Y-%m-%dT%H:%M")
        }

        for field, value in [
            ("package_id", data["package_id"]),
            ("city_id", data["city_id"]),
            ("category_id", data["category_id"])
        ]:
            element = driver.find_element(By.NAME, field)

            driver.execute_script("""
                arguments[0].value = arguments[1];
                arguments[0].dispatchEvent(
                    new Event('change', {bubbles: true})
                );
                arguments[0].dispatchEvent(
                    new Event('input', {bubbles: true})
                );
            """, element, value)

            event_form = driver.find_element(
                By.XPATH,
                "//button[normalize-space()='SIMPAN DRAFT' or normalize-space()='Simpan Draft']/ancestor::form"
            )

        for field in [
            "title",
            "description",
            "location",
            "registration_opens_at",
            "registration_deadline",
            "assessment_deadline",
            "decision_deadline",
            "starts_at",
            "ends_at"
        ]:
            element = event_form.find_element(By.NAME, field)

            driver.execute_script("""
                arguments[0].value = arguments[1];
                arguments[0].dispatchEvent(
                    new Event('input', {bubbles: true})
                );
                arguments[0].dispatchEvent(
                    new Event('change', {bubbles: true})
                );
            """, element, data[field])

            print(f"{field}: {element.get_attribute('value')}")

        for field in [
            "title",
            "description",
            "location",
            "starts_at",
            "ends_at",
            "registration_opens_at",
            "registration_deadline",
            "assessment_deadline",
            "decision_deadline"
        ]:
            value = event_form.find_element(
                By.NAME, field
            ).get_attribute("value")

            print(f"{field}: {value}")
        
        passed("TC-06", "Pengisian informasi dan jadwal event")
            
        # TC-07 Menyimpan Draft Event
        driver.save_screenshot(
            str(SCREENSHOTS / "e2e_03_form_event.png")
        )

        form = driver.find_element(
            By.XPATH,
            "//button[normalize-space()='SIMPAN DRAFT' or normalize-space()='Simpan Draft']/ancestor::form"
        )

        print("Form valid sebelum submit:", driver.execute_script(
            "return arguments[0].checkValidity()", form
        ))

        driver.execute_script("arguments[0].requestSubmit()", form)

        try:
            wait.until(
                lambda d: (
                    data["title"] in d.page_source
                    and len(d.find_elements(
                        By.XPATH,
                        f"//a[normalize-space()='{data['title']}']"
                    )) > 0
                )
            )

            passed("TC-07", "Penyimpanan draft event")

            result["event_title"] = data["title"]

            driver.save_screenshot(
                str(SCREENSHOTS / "e2e_03_success.png")
            )
            
            # TC-08
            driver.get(f"{BASE_URL}/organizer/events")

            event_link = wait.until(
                EC.presence_of_element_located((
                    By.XPATH,
                    f"//a[normalize-space()='{data['title']}']"
                ))
            )

            event_url = event_link.get_attribute("href")

            if not event_url:
                raise AssertionError("URL detail event tidak ditemukan.")

            driver.get(event_url)

            wait.until(
                EC.visibility_of_element_located((
                    By.XPATH,
                    f"//h1[contains(., '{data['title']}')]"
                ))
            )

            add_position = wait.until(
                EC.presence_of_element_located((
                    By.XPATH,
                    "//a[contains(normalize-space(), 'Tambah posisi')]"
                ))
            )

            position_url = add_position.get_attribute("href")

            if not position_url:
                raise AssertionError("URL tambah posisi tidak ditemukan.")

            driver.get(position_url)

            wait.until(
                EC.visibility_of_element_located((By.NAME, "name"))
            )

            passed("TC-08", "Membuka form tambah posisi")


            # TC-09 Mengisi data posisi
            position_name = "E2E Volunteer Coordinator"

            driver.find_element(
                By.NAME, "name"
            ).send_keys(position_name)

            driver.find_element(
                By.NAME, "quota"
            ).clear()

            driver.find_element(
                By.NAME, "quota"
            ).send_keys("5")

            description = driver.find_element(
                By.NAME, "description"
            )
            description.send_keys(
                "Bertanggung jawab membantu koordinasi kegiatan event."
            )

            # Tambah skill
            add_skill = wait.until(
                EC.presence_of_element_located((
                    By.XPATH,
                    "//button[contains(., 'Tambah skill')]"
                ))
            )

            driver.execute_script("arguments[0].click();", add_skill)

            skill_select = wait.until(
                EC.presence_of_element_located((
                    By.CSS_SELECTOR,
                    "select[name^='skills'][name$='[skill_id]']"
                ))
            )

            skill_options = Select(skill_select).options

            skill_option = next(
                (
                    option for option in skill_options
                    if "Event Coordinator" in option.text
                ),
                None
            )

            if skill_option is None:
                raise AssertionError(
                    "Skill Event Coordinator tidak ditemukan."
                )

            Select(skill_select).select_by_value(
                skill_option.get_attribute("value")
            )

            Select(
                driver.find_element(
                    By.NAME, "skills[0][minimum_level]"
                )
            ).select_by_value("beginner")

            Select(
                driver.find_element(
                    By.NAME, "skills[0][is_required]"
                )
            ).select_by_value("1")

            # Tambah persyaratan
            add_requirement = wait.until(
                EC.presence_of_element_located((
                    By.XPATH,
                    "//button[contains(., 'Tambah syarat')]"
                ))
            )

            driver.execute_script(
                "arguments[0].click();",
                add_requirement
            )

            requirement_name = wait.until(
                EC.visibility_of_element_located((
                    By.CSS_SELECTOR,
                    "input[name^='requirements'][name$='[name]'], textarea[name^='requirements'][name$='[name]']"
                ))
            )

            requirement_field = requirement_name.get_attribute("name")
            requirement_prefix = requirement_field.rsplit("[", 1)[0]

            requirement_name.send_keys(
                "Memiliki kemampuan komunikasi"
            )

            driver.find_element(
                By.NAME,
                f"{requirement_prefix}[description]"
            ).send_keys(
                "Mampu berkomunikasi dengan baik."
            )

            Select(
                driver.find_element(
                    By.NAME,
                    f"{requirement_prefix}[kind]"
                )
            ).select_by_value("manual")

            Select(
                driver.find_element(
                    By.NAME,
                    f"{requirement_prefix}[is_required]"
                )
            ).select_by_value("1")

            # TC-10 Menyimpan posisi
            form = driver.find_element(
                By.XPATH,
                "//button[contains(., 'Simpan posisi')]/ancestor::form"
            )

            print("\n=== MENGISI ULANG FIELD POSISI ===")

            form = driver.find_element(
                By.XPATH,
                "//button[contains(., 'Simpan posisi')]/ancestor::form"
            )

            fields = {
                "name": position_name,
                "quota": "5",
                "description": "Bertanggung jawab membantu koordinasi kegiatan event.",
                "requirements[0][name]": "Memiliki kemampuan komunikasi",
                "requirements[0][description]": "Mampu berkomunikasi dengan baik."
            }

            for field, value in fields.items():
                element = form.find_element(By.NAME, field)

                driver.execute_script("""
                    arguments[0].value = arguments[1];
                    arguments[0].dispatchEvent(
                        new Event('input', {bubbles: true})
                    );
                    arguments[0].dispatchEvent(
                        new Event('change', {bubbles: true})
                    );
                """, element, value)

                print(field, "=", element.get_attribute("value"))

            invalid_fields = driver.execute_script("""
                return Array.from(arguments[0].querySelectorAll(':invalid'))
                    .map(el => ({
                        name: el.name,
                        value: el.value,
                        message: el.validationMessage
                    }));
            """, form)

            print("Field tidak valid:", invalid_fields)

            if invalid_fields:
                raise AssertionError(
                    f"Form posisi tidak valid: {invalid_fields}"
                )

            driver.save_screenshot(
                str(SCREENSHOTS / "e2e_03_before_submit.png")
            )

            driver.execute_script(
                "arguments[0].requestSubmit()", form
            )

            wait.until(
                lambda d: d.current_url != position_url
            )

            passed("TC-10", "Penyimpanan posisi relawan")
            result["status"] = "PASS"

            driver.save_screenshot(
                str(SCREENSHOTS / "e2e_03_position_success.png")
            )
            
            
        except Exception:
            print("\nURL:", driver.current_url)
            print("\nISI HALAMAN:")
            print(driver.find_element(By.TAG_NAME, "body").text)

            print("\nPESAN VALIDASI:")
            for element in driver.find_elements(
                By.CSS_SELECTOR,
                "[role='alert'], .text-red-500, .text-danger, .invalid-feedback"
            ):
                if element.text.strip():
                    print("-", element.text.strip())

            driver.save_screenshot(
                str(SCREENSHOTS / "e2e_03_error_posisi.png")
            )
            raise

    finally:
        with open(
            REPORTS / "e2e_03_report.json",
            "w",
            encoding="utf-8"
        ) as file:
            json.dump(
                result,
                file,
                indent=4,
                ensure_ascii=False
            )

            print("\n===== HASIL E2E-03 =====")

            for step in result["steps"]:
                print(
                    f'{step["id"]} {step["description"]} : {step["status"]}'
                )

            print(f'\nSTATUS AKHIR : {result["status"]}')

            if "event_title" in result:
                print(f'EVENT       : {result["event_title"]}')

        driver.quit()


if __name__ == "__main__":
    run()
