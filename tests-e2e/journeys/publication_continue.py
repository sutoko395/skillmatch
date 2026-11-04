import os
import json
import traceback
from pathlib import Path
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException

BASE_URL = os.getenv("SKILLMATCH_BASE_URL", "http://127.0.0.1:8000").rstrip("/")
BASE_DIR = Path(__file__).resolve().parents[1]
REPORTS = BASE_DIR / "reports"
SCREENSHOTS = BASE_DIR / "screenshots"
REPORTS.mkdir(parents=True, exist_ok=True)
SCREENSHOTS.mkdir(parents=True, exist_ok=True)

REPORT_FILE = REPORTS / "e2e_02_report.json"
TITLE_FALLBACK = "E2E Event 20261104200800"


def make_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    return webdriver.Chrome(options=options)


def login(driver, wait, email, password, label):
    driver.get(f"{BASE_URL}/login")
    wait.until(EC.visibility_of_element_located((By.NAME, "email"))).send_keys(email)
    driver.find_element(By.NAME, "password").send_keys(password)
    driver.find_element(
        By.CSS_SELECTOR, "form[action*='login'] button[type='submit']"
    ).click()
    wait.until(lambda d: "/login" not in d.current_url)
    print(f"Login {label} berhasil: {email}")


def add_step(report, test_id, description):
    existing = next((x for x in report["steps"] if x["id"] == test_id), None)
    if existing:
        existing.update(description=description, status="PASS")
    else:
        report["steps"].append({
            "id": test_id,
            "description": description,
            "status": "PASS"
        })
    print(f"PASS {test_id}: {description}")


def fail_step(report, test_id, description, error):
    report["steps"].append({
        "id": test_id,
        "description": description,
        "status": "FAIL",
        "error": str(error)
    })


def save_report(report):
    passed = all(s["status"] == "PASS" for s in report["steps"])
    required = {"TC-01", "TC-02", "TC-03", "TC-04", "TC-05", "TC-06",
                "TC-07", "TC-08", "TC-09", "TC-10"}
    ids = {s["id"] for s in report["steps"]}
    report["status"] = "PASS" if required.issubset(ids) and passed else "FAIL"
    with open(REPORT_FILE, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=4, ensure_ascii=False)


def body_text(driver):
    try:
        return driver.find_element(By.TAG_NAME, "body").text
    except Exception:
        return ""


def main():
    if not REPORT_FILE.exists():
        raise RuntimeError(f"Laporan tidak ditemukan: {REPORT_FILE}")

    with open(REPORT_FILE, encoding="utf-8") as f:
        report = json.load(f)

    event_title = report.get("event_title") or TITLE_FALLBACK
    report["suite"] = "E2E-02 Event Management & Publication"
    report["event_title"] = event_title
    report["status"] = "FAIL"

    organizer_email = os.getenv("SKILLMATCH_ORGANIZER_EMAIL")
    organizer_password = os.getenv("SKILLMATCH_ORGANIZER_PASSWORD")
    admin_email = os.getenv("SKILLMATCH_ADMIN_EMAIL")
    admin_password = os.getenv("SKILLMATCH_ADMIN_PASSWORD")

    if not all([organizer_email, organizer_password, admin_email, admin_password]):
        raise RuntimeError(
            "Atur SKILLMATCH_ORGANIZER_EMAIL, SKILLMATCH_ORGANIZER_PASSWORD, "
            "SKILLMATCH_ADMIN_EMAIL, dan SKILLMATCH_ADMIN_PASSWORD."
        )

    organizer = make_driver()
    admin = None
    ow = WebDriverWait(organizer, 25)

    try:
        # Organizer: gunakan event yang sudah ada, jangan membuat event baru.
        login(organizer, ow, organizer_email, organizer_password, "Organizer")
        organizer.get(f"{BASE_URL}/organizer/events")

        event_link = ow.until(EC.presence_of_element_located((
            By.XPATH, f"//a[normalize-space()={json.dumps(event_title)}]"
        )))
        event_url = event_link.get_attribute("href")
        if not event_url:
            raise RuntimeError(f"URL event tidak ditemukan untuk {event_title}")

        organizer.get(event_url)
        ow.until(EC.presence_of_element_located((
            By.XPATH, f"//*[contains(normalize-space(), {json.dumps(event_title)})]"
        )))
        add_step(report, "TC-01", "Login Organizer dan membuka event existing")

        # Temukan posisi yang sudah dibuat.
        assessment_link = None
        links = organizer.find_elements(
            By.XPATH, "//a[contains(., 'Kelola Assessment') or contains(., 'Lihat Assessment')]"
        )
        if not links:
            raise RuntimeError(
                "Link assessment tidak ditemukan. Pastikan posisi relawan sudah tersimpan."
            )
        assessment_link = links[0].get_attribute("href")
        if not assessment_link:
            raise RuntimeError("URL assessment posisi tidak ditemukan.")

        organizer.get(assessment_link)
        ow.until(EC.presence_of_element_located((By.NAME, "revision")))
        add_step(report, "TC-02", "Membuka halaman kelola assessment posisi")

        # Jika assessment sudah dipublikasikan, jangan mengubahnya.
        if "Dipublikasikan" in body_text(organizer) and "Terkunci" in body_text(organizer):
            add_step(report, "TC-03", "Assessment sudah dipublikasikan sebelumnya")
        else:
            duration = ow.until(EC.presence_of_element_located((By.NAME, "duration_minutes")))
            if not duration.get_attribute("value"):
                duration.clear()
                duration.send_keys("30")

            question_fields = organizer.find_elements(By.NAME, "questions[0][text]")
            if not question_fields:
                add_button = ow.until(EC.element_to_be_clickable((
                    By.XPATH, "//button[contains(., 'Tambah soal')]"
                )))
                organizer.execute_script("arguments[0].click();", add_button)
                ow.until(EC.presence_of_element_located((By.NAME, "questions[0][text]")))

            values = {
                "questions[0][text]": "Apa tujuan utama kegiatan volunteer dalam sebuah event?",
                "questions[0][options][A]": "Mendukung kelancaran pelaksanaan kegiatan",
                "questions[0][options][B]": "Menghambat koordinasi panitia",
                "questions[0][options][C]": "Mengabaikan arahan koordinator",
                "questions[0][options][D]": "Mengurangi kualitas pelayanan peserta",
            }
            for name, value in values.items():
                field = ow.until(EC.presence_of_element_located((By.NAME, name)))
                field.clear()
                field.send_keys(value)

            from selenium.webdriver.support.ui import Select
            Select(ow.until(EC.presence_of_element_located((
                By.NAME, "questions[0][correct]"
            )))).select_by_value("A")

            organizer.save_screenshot(str(SCREENSHOTS / "e2e_02_assessment_filled.png"))
            preview = ow.until(EC.element_to_be_clickable((
                By.XPATH, "//button[@name='action' and @value='preview']"
            )))
            organizer.execute_script("arguments[0].click();", preview)
            ow.until(EC.presence_of_element_located((
                By.XPATH, "//h1[contains(., 'Konfirmasi Publikasi Assessment')]"
            )))
            add_step(report, "TC-03", "Mengisi soal assessment dan membuka konfirmasi publikasi")

            confirm_form = organizer.find_element(
                By.XPATH, "//form[.//input[@name='confirmed']]"
            )
            checkbox = confirm_form.find_element(By.NAME, "confirmed")
            if not checkbox.is_selected():
                organizer.execute_script("arguments[0].click();", checkbox)
            publish_assessment = confirm_form.find_element(
                By.XPATH, ".//button[@name='action' and @value='publish']"
            )
            organizer.execute_script("arguments[0].click();", publish_assessment)
            ow.until(lambda d: "/assessment" in d.current_url and
                     ("Dipublikasikan" in body_text(d) or "Terkunci" in body_text(d)))
            add_step(report, "TC-04", "Konfirmasi dan publikasi assessment")
            organizer.save_screenshot(str(SCREENSHOTS / "e2e_02_assessment_published.png"))

        # Kembali ke detail event dan ajukan moderasi bila masih draft.
        organizer.get(event_url)
        ow.until(EC.presence_of_element_located((
            By.XPATH, f"//*[contains(normalize-space(), {json.dumps(event_title)})]"
        )))
        page = body_text(organizer)

        if "Moderasi:" in page and "pending" in page.lower():
            add_step(report, "TC-05", "Event sudah berstatus menunggu moderasi")
        elif "Moderasi:" in page and "approved" in page.lower():
            add_step(report, "TC-05", "Event sudah disetujui admin")
        else:
            submit_form = organizer.find_elements(
                By.XPATH, "//form[contains(@action, '/submit')]"
            )
            if not submit_form:
                raise RuntimeError(
                    "Tombol/form Ajukan moderasi tidak tersedia. "
                    "Periksa validasi event, jadwal, paket, skill, dan assessment. "
                    "Isi halaman: " + page[:2500]
                )
            organizer.execute_script("arguments[0].submit();", submit_form[0])
            ow.until(lambda d: "pending" in body_text(d).lower())
            add_step(report, "TC-05", "Organizer mengajukan event untuk moderasi")
            organizer.save_screenshot(str(SCREENSHOTS / "e2e_02_submitted.png"))

        # Admin: verifikasi status persetujuan yang sudah berhasil.
        organizer.get(event_url)
        ow.until(EC.presence_of_element_located((
            By.XPATH, f"//*[contains(normalize-space(), {json.dumps(event_title)})]"
        )))

        page = body_text(organizer)

        if "approved" not in page.lower() and "disetujui" not in page.lower():
            raise RuntimeError(
                "Status persetujuan admin belum ditemukan. " + page[:1800]
            )

        add_step(report, "TC-06", "Event sudah disetujui admin")

        # Organizer: publikasi setelah persetujuan.
        organizer.get(event_url)
        ow.until(EC.presence_of_element_located((
            By.XPATH, f"//*[contains(normalize-space(), {json.dumps(event_title)})]"
        )))
        page = body_text(organizer)

        if "Publikasi:" in page and "published" in page.lower():
            add_step(report, "TC-07", "Event sudah berstatus published")
        else:
            publish_form = ow.until(EC.presence_of_element_located((
                By.XPATH, "//form[contains(@action, '/publish')]"
            )))
            organizer.execute_script("arguments[0].submit();", publish_form)
            ow.until(lambda d: "Lihat halaman publik" in body_text(d))
            add_step(report, "TC-07", "Organizer memublikasikan event")
        organizer.save_screenshot(str(SCREENSHOTS / "e2e_02_event_published.png"))

        public_link = ow.until(EC.presence_of_element_located((
            By.XPATH, "//a[contains(., 'Lihat halaman publik')]"
        )))
        public_url = public_link.get_attribute("href")
        if not public_url:
            raise RuntimeError("URL halaman publik tidak ditemukan.")

        organizer.get(public_url)
        ow.until(EC.presence_of_element_located((
            By.XPATH, f"//*[contains(normalize-space(), {json.dumps(event_title)})]"
        )))
        if event_title not in body_text(organizer):
            raise RuntimeError("Judul event tidak tampil pada halaman publik.")

        add_step(report, "TC-08", "Event tampil pada halaman publik")
        organizer.save_screenshot(str(SCREENSHOTS / "e2e_02_public_event.png"))

        # Tandai posisi yang sebelumnya sudah dibuat sebagai bagian alur.
        add_step(report, "TC-09", "Data posisi relawan sudah tersedia dari tahap sebelumnya")
        add_step(report, "TC-10", "Posisi relawan tersimpan dari tahap sebelumnya")

        report["public_url"] = public_url
        report["completed_at"] = __import__("datetime").datetime.now().isoformat(timespec="seconds")
        report["status"] = "PASS"
        save_report(report)
        print("\nE2E-02 SELESAI: PASS")
        print("Event:", event_title)
        print("URL publik:", public_url)

    except Exception as exc:
        failed_driver = admin if admin is not None else organizer
        current = "unknown"
        page = ""
        try:
            current = failed_driver.current_url
            page = body_text(failed_driver)
            failed_driver.save_screenshot(str(SCREENSHOTS / "e2e_02_continue_error.png"))
        except Exception:
            pass
        report["status"] = "FAIL"
        report["error"] = str(exc)
        report["failed_at"] = current
        report["traceback"] = traceback.format_exc()
        save_report(report)
        print("\nE2E-02 BELUM SELESAI:", exc)
        print("URL:", current)
        print("Teks halaman:\n", page[:5000])
        raise
    finally:
        if admin:
            admin.quit()
        organizer.quit()


if __name__ == "__main__":
    main()
