import os
import sys
import json
import re
from datetime import datetime
from pathlib import Path

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select

ROOT = Path(__file__).resolve().parents[1]
PROJECT_ROOT = ROOT.parent

sys.path.insert(0, str(ROOT / "config"))

from driver import create_driver, BASE_URL

SCREENSHOT_DIR = ROOT / "screenshots"
REPORT_DIR = ROOT / "reports"

SCREENSHOT_DIR.mkdir(parents=True, exist_ok=True)
REPORT_DIR.mkdir(parents=True, exist_ok=True)

results = []


def record(name, status, detail=""):
    results.append({
        "test": name,
        "status": status,
        "detail": detail
    })

    print(f"{status}: {name} {detail}")


def assert_visible(driver, by, selector):
    element = driver.find_element(by, selector)
    assert element.is_displayed(), f"Elemen tidak tampil: {selector}"
    return element


def run():
    driver, wait = create_driver()

    try:
        # TC-01: Homepage
        driver.get(BASE_URL)

        wait.until(
            EC.visibility_of_element_located(
                (By.ID, "hero-title")
            )
        )

        assert_visible(driver, By.ID, "paket")
        assert_visible(driver, By.ID, "home-search")

        record("TC-01 Homepage", "PASS")

        # TC-02: Katalog event
        event_link = wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "//a[contains(@href, '/events') and contains(., 'Jelajahi Event')]")
            )
        )
        event_link.click()

        wait.until(EC.url_contains("/events"))
        assert_visible(driver, By.NAME, "search")
        record("TC-02 Katalog Event", "PASS")

        # TC-03 sampai TC-06: Pencarian dan filter
        cards = driver.find_elements(
            By.CSS_SELECTOR,
            "article"
        )

        event_card = None

        for card in cards:
            links = card.find_elements(
                By.CSS_SELECTOR,
                "h3 a[href*='/events/']"
            )

            if links:
                event_card = card
                break

        if event_card is None:
            record(
                "TC-03 sampai TC-06 Pencarian dan Filter",
                "SKIP",
                "Belum ada event publik di katalog."
            )
        else:
            title = event_card.find_element(
                By.CSS_SELECTOR,
                "h3 a"
            ).text.strip()

            city = event_card.find_element(
                By.XPATH,
                ".//dt[normalize-space()='Kota']/following-sibling::dd"
            ).text.strip()

            category = event_card.find_element(
                By.CSS_SELECTOR,
                "div:first-child span:first-child"
            ).text.strip()

            date_text = event_card.find_element(
                By.XPATH,
                ".//dt[normalize-space()='Pelaksanaan']/following-sibling::dd"
            ).text.strip()

            # Search title
            search = driver.find_element(By.NAME, "search")
            search.send_keys(title)

            driver.find_element(
                By.CSS_SELECTOR,
                "form button[type='submit']"
            ).click()

            wait.until(
                EC.url_contains("search=")
            )

            assert title.lower() in driver.page_source.lower()

            record("TC-03 Pencarian Judul", "PASS")

            # Filter city
            driver.get(f"{BASE_URL}/events")

            city_select = Select(
                driver.find_element(By.NAME, "city_id")
            )

            city_option = next(
                (
                    option for option in city_select.options
                    if option.text.strip() == city
                ),
                None
            )

            if city_option and city_option.get_attribute("value"):
                city_select.select_by_value(
                    city_option.get_attribute("value")
                )

                driver.find_element(
                    By.CSS_SELECTOR,
                    "form button[type='submit']"
                ).click()

                wait.until(EC.url_contains("city_id="))

                record("TC-04 Filter Kota", "PASS")
            else:
                record("TC-04 Filter Kota", "SKIP", "Opsi kota tidak ditemukan.")

            # Filter category
            driver.get(f"{BASE_URL}/events")

            category_select = Select(
                driver.find_element(By.NAME, "category_id")
            )

            category_option = next(
                (
                    option for option in category_select.options
                    if option.text.strip() == category
                ),
                None
            )

            if category_option and category_option.get_attribute("value"):
                category_select.select_by_value(
                    category_option.get_attribute("value")
                )

                driver.find_element(
                    By.CSS_SELECTOR,
                    "form button[type='submit']"
                ).click()

                wait.until(EC.url_contains("category_id="))

                record("TC-05 Filter Kategori", "PASS")
            else:
                record("TC-05 Filter Kategori", "SKIP", "Opsi kategori tidak ditemukan.")

            # TC-06: Filter tanggal
            driver.get(f"{BASE_URL}/events")

            match = re.search(
                r"(\d{2}/\d{2}/\d{4})",
                date_text
            )

            if match:
                date_value = datetime.strptime(
                    match.group(1),
                    "%d/%m/%Y"
                ).strftime("%Y-%m-%d")

                date_input = driver.find_element(
                    By.NAME,
                    "date"
                )

                driver.execute_script(
                    """
                    arguments[0].value = arguments[1];
                    arguments[0].dispatchEvent(
                        new Event('input', { bubbles: true })
                    );
                    arguments[0].dispatchEvent(
                        new Event('change', { bubbles: true })
                    );
                    """,
                    date_input,
                    date_value
                )

                date_input.find_element(
                    By.XPATH,
                    "./ancestor::form"
                ).find_element(
                    By.CSS_SELECTOR,
                    "button[type='submit'], button"
                ).click()

                wait.until(
                    lambda d: "date=" in d.current_url
                )

                record("TC-06 Filter Tanggal", "PASS")
            else:
                record(
                    "TC-06 Filter Tanggal",
                    "SKIP",
                    f"Format tanggal tidak ditemukan: {date_text}"
                )
                
        # TC-07: Reset filter
        driver.get(f"{BASE_URL}/events?search=test&city_id=1&category_id=1")

        reset_link = wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "//a[contains(normalize-space(), 'Reset')]")
            )
        )

        reset_link.click()

        wait.until(
            lambda d: "search=" not in d.current_url
            and "city_id=" not in d.current_url
            and "category_id=" not in d.current_url
            and "date=" not in d.current_url
        )

        search_input = driver.find_element(By.NAME, "search")

        assert search_input.get_attribute("value") in ("", None)

        record("TC-07 Reset Filter", "PASS")        

        # TC-08: Detail event
        driver.get(f"{BASE_URL}/events")

        detail_links = driver.find_elements(
            By.CSS_SELECTOR,
            "article h3 a[href*='/events/']"
        )

        if detail_links:
            detail_links[0].click()

            wait.until(
                EC.visibility_of_element_located(
                    (By.CSS_SELECTOR, "h1")
                )
            )

            record("TC-08 Detail Event", "PASS")
        else:
            record("TC-08 Detail Event", "SKIP", "Tidak ada event publik.")

        # TC-09: Form registrasi
        driver.get(f"{BASE_URL}/register")

        assert_visible(driver, By.NAME, "role")
        assert_visible(driver, By.ID, "name")
        assert_visible(driver, By.ID, "email")

        assert driver.find_elements(
            By.CSS_SELECTOR,
            "input[name='password']"
        )

        assert driver.find_elements(
            By.NAME,
            "password_confirmation"
        )

        record("TC-09 Form Registrasi", "PASS")


        # TC-10: Form login
        driver.get(f"{BASE_URL}/login")

        assert_visible(driver, By.ID, "email")
        assert driver.find_elements(
            By.CSS_SELECTOR,
            "input[name='password']"
        )

        forgot_link = assert_visible(
            driver,
            By.LINK_TEXT,
            "Lupa kata sandi?"
        )

        record("TC-10 Form Login", "PASS")

        # TC-11: Halaman Reset Password
        forgot_link.click()

        wait.until(
            lambda d: "forgot-password" in d.current_url
        )

        assert_visible(driver, By.NAME, "email")

        reset_form = driver.find_element(
            By.CSS_SELECTOR,
            "form"
        )

        assert reset_form.find_elements(
            By.CSS_SELECTOR,
            "button[type='submit'], input[type='submit']"
        ), "Tombol kirim reset password tidak ditemukan."

        record(
            "TC-11 Reset Password",
            "PASS",
            "Halaman lupa password dan form pengiriman tautan reset tersedia."
        )

        # TC-12: Paket Organizer
        driver.get(BASE_URL)

        package_section = assert_visible(
            driver,
            By.ID,
            "paket"
        )

        package_cards = package_section.find_elements(
            By.CSS_SELECTOR,
            "article"
        )

        assert package_cards, "Paket Organizer belum ditampilkan."

        record(
            "TC-12 Informasi Paket Organizer",
            "PASS",
            f"{len(package_cards)} paket ditampilkan."
        )


        driver.save_screenshot(
            str(SCREENSHOT_DIR / "e2e_01_success.png")
        )

    except Exception as error:
        driver.save_screenshot(
            str(SCREENSHOT_DIR / "e2e_01_failure.png")
        )

        record(
            "E2E-01",
            "FAIL",
            str(error)
        )

        raise

    finally:
        report_file = REPORT_DIR / "e2e_01_report.json"

        report_file.write_text(
            json.dumps(
                results,
                indent=4,
                ensure_ascii=False
            ),
            encoding="utf-8"
        )

        driver.quit()

        print("\n===== HASIL E2E-01 =====")

        for result in results:
            print(
                f"{result['test']} : {result['status']}"
            )


if __name__ == "__main__":
    run()