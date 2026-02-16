# Shipping Tracking System Implementation Plan (URL Scraping Method)

This document outlines the architecture for fetching real-time tracking data directly from carrier websites (Aramex, UPS, USPS, FedEx) using their public tracking URLs.

## 1. Objectives
*   Extract live tracking status and history directly from carrier URLs.
*   No dependency on paid third-party APIs (like AfterShip).
*   Automatic background updates of tracking history every few hours.
*   Standardized "Timeline View" inside the CRM for all carriers.

---

## 2. Technical Architecture

### A. Core Components
1.  **Shipping Parser Engine (`App\Services\ShippingParser`):** A custom service containing individual "Parsers" for each carrier.
2.  **Data Extraction Strategy:**
    *   Use **GuzzleHTTP** to fetch page content.
    *   Use **DOMCrawler** (Symfony) to parse the HTML and find status/history elements.
    *   *Note:* If carriers use heavy JavaScript (like FedEx/UPS), we may need a headless browser bridge.
3.  **Database Storage:**
    *   `tracking_url`: The link provided by the admin.
    *   `tracking_status`: Current milestone (e.g., "Picked Up").
    *   `tracking_history`: JSON cache of the full timeline.

### B. Supported Carriers (Initial Scope)
*   **Aramex**: URL-based HTML parsing.
*   **UPS**: URL-based scraping.
*   **USPS**: Public tracking page extraction.
*   **FedEx**: Public view parsing.

---

## 3. Implementation Phases

### Phase 1: Database Migration
Add tracking-specific columns to the `orders` table:
```php
Schema::table('orders', function (Blueprint $table) {
    $table->text('tracking_url')->nullable();      // The direct link
    $table->string('tracking_status')->nullable(); // Standardized status
    $table->json('tracking_history')->nullable();  // Parsed history timeline
    $table->timestamp('last_tracker_sync')->nullable();
});
```

### Phase 2: The Parser Module
Develop unique logic for each carrier. For example, the Aramex parser will look for specific CSS selectors like `.tracking-result` or `.timeline-item` to get:
*   **Date/Time**
*   **Status Text**
*   **Location**

### Phase 3: Automated Sync (Cron Job)
A scheduled command (`shipping:sync`) will:
1.  Identify orders with a `tracking_url`.
2.  Visit the URL in the background.
3.  Extract the data and update `tracking_history`.
4.  If the status changes to "Delivered", update the main Order Status.

### Phase 4: UI Development (The Tracking View)
Modify `orders/show.blade.php` to include:
*   **Status Indicator:** High-level current status.
*   **Shipping Timeline:** A vertical list showing the package's journey (Extracted from `tracking_history`).
*   **Refresh Button:** Manual trigger to fetch data immediately.

---

## 4. Key Challenges & Solutions
*   **Anti-Bot Protection:** Carriers like UPS might block server-side requests.
    *   *Solution:* Use rotating User-Agents or a basic proxy if needed.
*   **Layout Changes:** If Aramex changes their website design, the scraper breaks.
    *   *Solution:* Centralized parser logic so we only need to update one file (`AramexParser.php`) to fix it.

---

## 5. Next Steps
1.  **Provide Sample URLs:** Admin to provide one working URL for Aramex, UPS, USPS, and FedEx.
2.  **Verification:** I will check each URL to see if the data can be scraped without obstacles.
3.  **Implementation:** Start with Phase 1 (Database) once URLs are verified.
