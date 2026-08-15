#!/Users/swang/myenv/bin/python
from playwright.sync_api import sync_playwright
import os
from datetime import datetime

USERNAME = "wangshengli"
PASSWORD = "Yjwlejll11!!"
SAVE_FOLDER = "./Documents/fidelity_daily"
os.makedirs(SAVE_FOLDER, exist_ok=True)

def download_fidelity_portfolio_csv():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False) # set headless=True for background; harder for MFA
        context = browser.new_context(accept_downloads=True)
        page = context.new_page()

        # Visit Fidelity login
        page.goto("https://digital.fidelity.com/prgw/digital/login/full-page")
        page.wait_for_timeout(2000)

        # Enter credentials
        # page.get_by_label("Username").fill("wangshengli")
        # page.get_by_label("Password").fill("Yjwlejll11!!")
        page.get_by_label("Username").fill(USERNAME)
        page.get_by_label("Password").fill(PASSWORD)
        page.get_by_role("button", name="Log in").click()

        # PAUSE FOR MFA: you must  complete verification manually here
        print("Waiting for you to complete MFA verification...")
        page.wait_for_url("**/portfolio/**", timeout=120000) # wait up to 2 minutes to finish login

        # Navigate to Positions page
        page.goto("https://digital.fidelity.com/ftgw/digital/portfolio/positions")
        page.wait_for_timeout(3000)

        # Trigger CSV download
        with page.expect_download() as download_handler:
            # Button label: "Download Positions"
            page.get_by_label("Download Positions").click()
        download = download_handler.value

        # Save file with timestamp
        timestamp = datetime.now().strftime("%Y%m%d_%H%M")
        output_path = os.path.join(SAVE_FOLDER, f"fidelity_portfolio_{timestamp}.csv")
        download.save_as(output_path)
        print(f"CSV saved: {output_path}")

        browser.close()

if __name__ == "__main__":
    download_fidelity_portfolio_csv()
