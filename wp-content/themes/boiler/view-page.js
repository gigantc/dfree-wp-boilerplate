const { chromium } = require('playwright');

(async () => {
  // Launch browser in headed mode so you can see it
  const browser = await chromium.launch({
    headless: false,
    slowMo: 100 // Slow down by 100ms to see what's happening
  });

  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 }
  });

  const page = await context.newPage();

  console.log('Navigating to http://localhost:3000/...');

  try {
    await page.goto('http://localhost:3000/', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    console.log('✓ Page loaded successfully!');
    console.log('Title:', await page.title());

    // Take a screenshot
    await page.screenshot({ path: 'screenshot.png', fullPage: true });
    console.log('✓ Screenshot saved to screenshot.png');

    // Keep browser open for 30 seconds so you can interact
    console.log('\nBrowser will stay open for 30 seconds...');
    console.log('Press Ctrl+C to close earlier');
    await page.waitForTimeout(30000);

  } catch (error) {
    console.error('Error loading page:', error.message);
  }

  await browser.close();
  console.log('Browser closed.');
})();
