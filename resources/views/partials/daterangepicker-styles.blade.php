{{--
Custom Date Range Picker Styles
Match project's indigo theme (#6366f1)
Include this partial with @include('partials.daterangepicker-styles')
--}}

<style>
    /* Date Range Picker Custom Styles */
    .daterangepicker {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        border: none;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.2), 0 10px 25px rgba(0, 0, 0, 0.08);
        z-index: 9999;
        overflow: hidden;
    }

    .daterangepicker::before,
    .daterangepicker::after {
        display: none;
    }

    /* Ranges sidebar */
    .daterangepicker .ranges {
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        padding: 0.5rem 0;
    }

    .daterangepicker .ranges li {
        padding: 10px 18px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        margin: 0;
    }

    .daterangepicker .ranges li:hover {
        background: rgba(99, 102, 241, 0.08);
        color: #6366f1;
        border-left-color: #6366f1;
    }

    .daterangepicker .ranges li.active {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        border-left-color: transparent;
    }

    /* Calendar container */
    .daterangepicker .drp-calendar {
        padding: 1rem;
        max-width: none;
    }

    .daterangepicker .drp-calendar.left,
    .daterangepicker .drp-calendar.right {
        padding: 0.75rem 1rem;
    }

    /* Month/Year header */
    .daterangepicker .calendar-table .month {
        font-weight: 700;
        font-size: 1rem;
        color: #1e293b;
        padding: 0.75rem 0;
    }

    /* Navigation arrows */
    .daterangepicker .calendar-table th.prev,
    .daterangepicker .calendar-table th.next {
        color: #6366f1;
        font-size: 1.25rem;
        transition: all 0.2s;
        border-radius: 8px;
        cursor: pointer;
    }

    .daterangepicker .calendar-table th.prev:hover,
    .daterangepicker .calendar-table th.next:hover {
        background: rgba(99, 102, 241, 0.1);
        color: #4f46e5;
    }

    /* Day names header */
    .daterangepicker .calendar-table th {
        font-weight: 600;
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem;
    }

    /* Calendar day cells */
    .daterangepicker .calendar-table td {
        font-size: 0.875rem;
        width: 36px;
        height: 36px;
        line-height: 36px;
        padding: 0;
        border-radius: 8px;
        color: #1e293b;
        transition: all 0.15s ease;
    }

    .daterangepicker .calendar-table td.available:hover {
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
    }

    .daterangepicker .calendar-table td.in-range {
        background: rgba(99, 102, 241, 0.12);
        color: #6366f1;
        border-radius: 0;
    }

    .daterangepicker .calendar-table td.active,
    .daterangepicker .calendar-table td.active:hover {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
    }

    .daterangepicker .calendar-table td.start-date {
        border-radius: 8px 0 0 8px;
    }

    .daterangepicker .calendar-table td.end-date {
        border-radius: 0 8px 8px 0;
    }

    .daterangepicker .calendar-table td.start-date.end-date {
        border-radius: 8px;
    }

    .daterangepicker .calendar-table td.off {
        color: #cbd5e1;
    }

    .daterangepicker .calendar-table td.disabled {
        color: #e2e8f0;
        cursor: not-allowed;
    }

    /* Today highlight */
    .daterangepicker .calendar-table td.today {
        font-weight: 700;
    }

    .daterangepicker .calendar-table td.today::after {
        content: '';
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 4px;
        background: #6366f1;
        border-radius: 50%;
    }

    /* Weekend styling */
    .daterangepicker .calendar-table td.weekend {
        color: #ef4444;
    }

    /* Buttons */
    .daterangepicker .drp-buttons {
        border-top: 1px solid #e2e8f0;
        padding: 1rem;
        background: #f8fafc;
    }

    .daterangepicker .drp-buttons .btn {
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.625rem 1.25rem;
        border-radius: 10px;
        transition: all 0.2s;
    }

    .daterangepicker .applyBtn {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
        color: #fff;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .daterangepicker .applyBtn:hover {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
    }

    .daterangepicker .cancelBtn {
        background: #fff;
        border: 2px solid #e2e8f0;
        color: #64748b;
    }

    .daterangepicker .cancelBtn:hover {
        border-color: #6366f1;
        color: #6366f1;
        background: rgba(99, 102, 241, 0.05);
    }

    /* Selected range display */
    .daterangepicker .drp-selected {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.875rem;
    }

    /* Input field styling */
    .date-range-input {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        font-size: 0.95rem;
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 280px;
    }

    .date-range-input:hover {
        border-color: #6366f1;
    }

    .date-range-input:focus,
    .date-range-input.active {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .date-range-input i {
        color: #6366f1;
        font-size: 1.125rem;
    }

    .date-range-input .date-text {
        flex: 1;
    }

    .date-range-input .date-text.placeholder {
        color: #94a3b8;
    }

    /* Dropdown arrow */
    .date-range-input::after {
        content: '\F282';
        font-family: 'bootstrap-icons';
        color: #64748b;
        font-size: 0.875rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .daterangepicker {
            width: 100%;
            max-width: 320px;
        }

        .daterangepicker .drp-calendar {
            max-width: 100%;
        }

        .date-range-input {
            min-width: 100%;
        }
    }
</style>