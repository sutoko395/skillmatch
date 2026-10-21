
import os
import uuid
from datetime import datetime, timedelta

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC


BASE_URL = os.getenv("SKILLMATCH_BASE_URL", "http://127.0.0.1:8000")
EMAIL = os.getenv("SKILLMATCH_ORGANIZER_EMAIL")
PASSWORD = os.getenv("SKILLMATCH_ORGANIZER_PASSWORD")

if not EMAIL or not PASSWORD:
    raise RuntimeError(
        "Atur SKILLMATCH_ORGANIZER_EMAIL dan "
        "SKILLMATCH_ORGANIZER_PASSWORD terlebih dahulu."
    )

os.makedirs("tests-e2e/screenshots", exist_ok=True)

suffix = uuid.uuid4().hex[:8]
title = f"Event Selenium Uji {suffix}"

now = datetime.now()
opens = now + timedelta(minutes=2)
starts = now + timedelta(days=3)
deadline = starts - timedelta(hours=2)
ends = starts + timedelta(hours=3)

options = webdriver.ChromeOptions()
options.add_argument("--start-maximized")

driver = webdriver.Chrome(options=options)
wait = WebDriverWait(driver, 20)

try:
    driver.get(f"{BASE_URL}/login")

    wait.until(
        EC.visibility_of_element_located((By.ID, "email"))
    ).send_keys(EMAIL)

    driver.find_element(By.ID, "password").send_keys(PASSWORD)
    driver.find_element(
        By.CSS_SELECTOR, "form button[type='submit']"
    ).click()

    wait.until(lambda d: "/login" not in d.current_url)

    driver.get(f"{BASE_URL}/organizer/events/create")

    wait.until(
        EC.visibility_of_element_located((By.NAME, "title"))
    )

    Select(driver.find_element(By.ID, "package_id")).select_by_value("2")
    Select(driver.find_element(By.ID, "city_id")).select_by_value("1")
    Select(driver.find_element(By.NAME, "category_id")).select_by_value("2")

    driver.find_element(By.NAME, "title").send_keys(title)
    driver.find_element(By.NAME, "description").send_keys(
        "Pengujian otomatis Selenium untuk pembuatan draft event SkillMatch."
    )
    driver.find_element(By.NAME, "location").send_keys(
        "Aula Pengujian, Malang"
    )

    fields = {
        "starts_at": starts,
        "ends_at": ends,
        "registration_opens_at": opens,
        "registration_deadline": deadline,
    }

    for name, value in fields.items():
        element = wait.until(
            EC.element_to_be_clickable((By.NAME, name))
        )
        formatted_value = value.strftime("%Y-%m-%dT%H:%M")

        driver.execute_script(
            """
            const element = arguments[0];
            element.value = arguments[1];
            element.dispatchEvent(new Event('input', { bubbles: true }));
            element.dispatchEvent(new Event('change', { bubbles: true }));
            """,
            element,
            formatted_value,
        )

        actual_value = element.get_attribute("value")
        if actual_value != formatted_value:
            raise AssertionError(
                f"Tanggal {name} tidak sesuai: "
                f"diharapkan {formatted_value}, didapat {actual_value}"
            )

    submit = wait.until(
        EC.element_to_be_clickable(
            (
                By.XPATH,
                "//form//button[@type='submit' and "
                ".//*[contains(normalize-space(.), 'Simpan draft')]]",
            )
        )
    )

    driver.execute_script(
        "arguments[0].scrollIntoView({block: 'center'});", submit
    )
    submit.click()

    wait.until(
        lambda d: "/organizer/events/create" not in d.current_url
    )

    wait.until(
        EC.presence_of_element_located(
            (By.XPATH, f"//*[contains(normalize-space(.), '{title}')]")
        )
    )

    print("PASS: Draft event ditemukan setelah penyimpanan.")
    print(f"Judul event: {title}")
    print(f"URL hasil: {driver.current_url}")

except Exception:
    driver.save_screenshot(
        "tests-e2e/screenshots/create_event_failure.png"
    )
    print("FAIL: Pengujian tidak berhasil diverifikasi.")
    print(f"URL terakhir: {driver.current_url}")
    raise

finally:
    driver.quit()
