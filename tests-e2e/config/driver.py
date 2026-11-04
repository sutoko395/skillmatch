import os
from selenium import webdriver
from selenium.webdriver.support.ui import WebDriverWait


BASE_URL = os.getenv(
    "SKILLMATCH_BASE_URL",
    "http://127.0.0.1:8000"
)


def create_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)
    wait = WebDriverWait(driver, 15)

    return driver, wait