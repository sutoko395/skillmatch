
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


class AuthPage:
    
    def logout(self):
        self.driver.get(f"{self.base_url}/organizer/aktivitas")

        logout_form = self.wait.until(
            EC.presence_of_element_located(
                (By.CSS_SELECTOR, "form[action*='logout']")
            )
        )

        logout_form.submit()

        self.wait.until(
            lambda driver: "/login" in driver.current_url
        )

        return self.driver.current_url

    def __init__(self, driver, base_url):
        self.driver = driver
        self.base_url = base_url.rstrip("/")
        self.wait = WebDriverWait(driver, 15)

    def login(self, email, password):
        self.driver.get(f"{self.base_url}/login")

        email_input = self.wait.until(
            EC.visibility_of_element_located((By.NAME, "email"))
        )
        password_input = self.wait.until(
            EC.visibility_of_element_located((By.NAME, "password"))
        )

        email_input.clear()
        email_input.send_keys(email)

        password_input.clear()
        password_input.send_keys(password)

        self.driver.find_element(
            By.CSS_SELECTOR, "form[action*='login'] button[type='submit']"
        ).click()

        self.wait.until(
            lambda driver: "/login" not in driver.current_url
        )

        return self.driver.current_url
