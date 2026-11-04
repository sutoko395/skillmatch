
from datetime import datetime, timedelta
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC


class EventPage:
    def __init__(self, driver, base_url):
        self.driver = driver
        self.base_url = base_url.rstrip("/")
        self.wait = WebDriverWait(driver, 15)

    def open_events(self):
        self.driver.get(f"{self.base_url}/organizer/events")
        self.wait.until(
            EC.visibility_of_element_located((By.TAG_NAME, "h1"))
        )

    def open_create_modal(self):
        button = self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "//button[normalize-space()='Buat Event']")
            )
        )

        button.click()

        self.driver.save_screenshot(
            "tests-e2e/screenshots/modal_debug.png"
        )

        print("Alpine tersedia:", self.driver.execute_script(
            "return typeof window.Alpine"
        ))

        print("Status modal:", self.driver.execute_script("""
            const element = document.querySelector('[x-data*="openCreateEvent"]');
            return element ? element.__x?.$data?.openCreateEvent
                ?? element.getAttribute('x-data') : 'Element tidak ditemukan';
        """))

        print("Heading halaman:", self.driver.find_elements(
            By.TAG_NAME, "h2"
        ) and [
            item.text for item in self.driver.find_elements(By.TAG_NAME, "h2")
        ])

        print("Form event:", self.driver.execute_script("""
            return [...document.querySelectorAll('form')]
                .map(form => ({
                    action: form.action,
                    display: getComputedStyle(form).display
                }));
        """))

    def get_select_options(self, field_name):
        element = self.wait.until(
            EC.presence_of_element_located((By.NAME, field_name))
        )

        return [
            {
                "value": option.get_attribute("value"),
                "text": option.get_attribute("textContent").strip()
            }
            for option in Select(element).options
            if option.get_attribute("value")
        ]

    def get_package_options(self):
        return self.get_select_options("package_id")

    def get_city_options(self):
        return self.get_select_options("city_id")

    def get_category_options(self):
        return self.get_select_options("category_id")

    def fill_event_form(self, event_data):
        fields = [
            "title",
            "description",
            "location",
            "starts_at",
            "ends_at",
            "registration_opens_at",
            "registration_deadline",
            "assessment_deadline",
            "decision_deadline",
        ]

        package = self.wait.until(
            EC.presence_of_element_located((By.NAME, "package_id"))
        )

        self.driver.execute_script("""
            const select = arguments[0];
            select.value = arguments[1];
            select.dispatchEvent(new Event('change', { bubbles: true }));
            select.dispatchEvent(new Event('input', { bubbles: true }));
        """, package, str(event_data["package_id"]))

        city = self.wait.until(
            EC.presence_of_element_located((By.NAME, "city_id"))
        )

        self.driver.execute_script("""
            const select = arguments[0];
            select.value = arguments[1];
            select.dispatchEvent(new Event('change', { bubbles: true }));
            select.dispatchEvent(new Event('input', { bubbles: true }));
        """, city, str(event_data["city_id"]))

        category = self.wait.until(
            EC.presence_of_element_located((By.NAME, "category_id"))
        )

        self.driver.execute_script("""
            const select = arguments[0];
            select.value = arguments[1];
            select.dispatchEvent(new Event('change', { bubbles: true }));
            select.dispatchEvent(new Event('input', { bubbles: true }));
        """, category, str(event_data["category_id"]))

        for field in fields:
            element = self.wait.until(
                EC.visibility_of_element_located((By.NAME, field))
            )
            element.clear()
            element.send_keys(str(event_data[field]))

    def submit_event(self):
        button = self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "//button[normalize-space()='Simpan Draft']")
            )
        )
        button.click()

    def get_event_titles(self):
        links = self.driver.find_elements(
            By.CSS_SELECTOR,
            "a[href*='/organizer/events/']"
        )
        return [link.text.strip() for link in links if link.text.strip()]

    def open_event(self, event_title):
        link = self.wait.until(
            EC.element_to_be_clickable(
                (
                    By.XPATH,
                    f"//a[normalize-space()={self._xpath_literal(event_title)}]"
                )
            )
        )
        link.click()

        self.wait.until(
            EC.presence_of_element_located(
                (By.XPATH, f"//*[normalize-space()={self._xpath_literal(event_title)}]")
            )
        )

    @staticmethod
    def generate_event_data(package_id, city_id, category_id):
        now = datetime.now().replace(second=0, microsecond=0)

        registration_open = now + timedelta(days=1)
        registration_deadline = now + timedelta(days=3)
        assessment_deadline = now + timedelta(days=4)
        decision_deadline = now + timedelta(days=5)
        event_start = now + timedelta(days=6)
        event_end = now + timedelta(days=6, hours=8)

        return {
            "package_id": package_id,
            "city_id": city_id,
            "category_id": category_id,
            "title": f"E2E Event {now:%Y%m%d%H%M}",
            "description": "Event untuk pengujian otomatis Selenium E2E.",
            "location": "Gedung Pengujian SkillMatch, Malang",
            "registration_opens_at": registration_open.strftime("%Y-%m-%dT%H:%M"),
            "registration_deadline": registration_deadline.strftime("%Y-%m-%dT%H:%M"),
            "assessment_deadline": assessment_deadline.strftime("%Y-%m-%dT%H:%M"),
            "decision_deadline": decision_deadline.strftime("%Y-%m-%dT%H:%M"),
            "starts_at": event_start.strftime("%Y-%m-%dT%H:%M"),
            "ends_at": event_end.strftime("%Y-%m-%dT%H:%M"),
        }

    @staticmethod
    def _xpath_literal(value):
        if "'" not in value:
            return f"'{value}'"
        if '"' not in value:
            return f'"{value}"'
        parts = value.split("'")
        return "concat(" + ', "\'", '.join(f"'{part}'" for part in parts) + ")"
