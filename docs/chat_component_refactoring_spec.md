# Technical Refactoring Specification: Chat Component Optimization

---

> **⚠️ MANDATORY PREREQUISITE — READ BEFORE EXECUTION**
>
> **Skill:** `code-refactoring-refactor-clean`
>
> Before executing ANY section of this plan, you MUST load and apply the
> `code-refactoring-refactor-clean` skill. This skill enforces:
> - Incremental, behavior-stable refactoring (no big-bang rewrites)
> - Clean code principles & SOLID design patterns
> - Small, reviewable diffs at each step
> - Test/verification after every change slice
>
> **Target File:** `resources/js/components/Chat.vue`
>
> **How to apply:** Assess the component for code smells and dependencies first,
> then propose a refactor plan with ordered steps matching Sections 2–5 below.
> Apply changes in small slices, keep behavior stable between each slice, and
> verify no regressions before moving to the next section.

---

## 1. Overview
The target is a large Vue 3 Single File Component (SFC) handling a full-featured chat system. The component currently suffers from "God Component" syndrome, where state management, WebSocket logic, UI resizing, and message formatting are all coupled in one file.

**Goal:** Improve performance (FPS), reduce CPU overhead, eliminate layout thrashing, and modularize the architecture.

---

## 2. Architectural Refactoring (Modularization)
The component is too large for maintainable development. **Split the logic into the following Composables:**

*   **`useChatMessages.js`**: Handle API calls for channels/messages, pagination logic (`loadOlderMessages`), and the local `messages` state.
*   **`useChatWebsockets.js`**: Handle all `window.Echo` listeners, `MessageSent`, `MessageReacted`, and the typing indicator logic.
*   **`useChatUI.js`**: Handle sidebar toggles, modal states, and the resizing logic for the Info and Thread panels.
*   **`useChatFormatting.js`**: Extract `formatMessageWithMentions`, `avatarInitials`, and `formatDate` into pure utility functions.

---

## 3. Critical Performance Fixes

### Issue A: The `typingTick` Global Re-render
*   **Problem:** A `setInterval` increments `typingTick` every second to force the `typingLabel` computed property to update. This causes the entire component (and header) to re-render every second regardless of activity.
*   **Fix:** Remove `typingTick` and the interval.
*   **Implementation:** Inside the `.UserTyping` WebSocket listener, add the user to the `typingUsers` object and simultaneously trigger a `setTimeout` for 3.5 seconds to delete that specific user from the object. This makes the UI event-driven rather than poll-driven.

### Issue B: $O(n \log n)$ Sorting on Every Message
*   **Problem:** `messages.value.sort()` is called every time a new message is received via WebSockets or sent by the user.
*   **Fix:** Remove the sort call from the message reception flow.
*   **Implementation:** Since messages arrive chronologically, use `.push()` for new messages. Only perform a sort during the initial channel load if the API data is unordered.

### Issue C: Redundant HTML Formatting & Sanitization
*   **Problem:** `formatMessageWithMentions` (which uses heavy Regex and `DOMPurify.sanitize`) is called directly in the template. It runs on every single render cycle for every message in the list.
*   **Fix:** Memoize the formatted HTML.
*   **Implementation:** 
    1. Move the formatting logic to a helper function.
    2. When messages are loaded from the API or received via WebSocket, add a property: `message.formattedBody = formatMessageBody(message)`.
    3. In the template, change `v-html="formatMessageWithMentions(message)"` to `v-html="message.formattedBody"`.

---

## 4. UI & Layout Optimizations

### Issue D: Layout Thrashing (Reflow) during Resizing
*   **Problem:** The `startResizeInfo` and `startResizeThread` functions manually update `.style.width` on multiple DOM elements inside `requestAnimationFrame`. This forces the browser to recalculate the entire page layout (Reflow) repeatedly.
*   **Fix:** Use **CSS Variables** for dynamic widths.
*   **Implementation:** 
    1. Define `--info-sidebar-width` and `--thread-sidebar-width` on the root container.
    2. In the JS resize handler, update only the CSS variable on the root element: `document.documentElement.style.setProperty('--info-sidebar-width', \`${newWidth}px\`)`.
    3. Update CSS to use `width: var(--info-sidebar-width)` and `width: calc(100% - var(--left-sidebar-width) - var(--info-sidebar-width))`.

---

## 5. Technical Debt & Stability

### Issue E: Memory Leaks & Observers
*   **Problem:** `ResizeObserver` and `window` listeners are initialized in a way that may lead to orphaned observers if the component is mounted/unmounted rapidly.
*   **Fix:** Strict lifecycle cleanup.
*   **Implementation:** Ensure all observers are stored in a ref and explicitly `.disconnect()` them in `onBeforeUnmount`.

### Issue F: PDF.js Worker Stability
*   **Problem:** The current `URL` import for the PDF worker is prone to breaking in production builds.
*   **Fix:** Use a stable CDN fallback for the `GlobalWorkerOptions.workerSrc`.

---

## 6. Summary of Expected Outcomes
1.  **CPU Usage:** Significant drop by removing the 1s tick.
2.  **Scroll Smoothness:** Elimination of jank during message reception (due to removed `.sort()`).
3.  **Render Speed:** Massive increase in rendering speed by pre-calculating `formattedBody`.
4.  **Resize Fluidity:** Smoother panel resizing by moving logic from JS DOM manipulation to CSS Variables.
