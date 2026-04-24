# Invoice Terms & Conditions Checkbox Plan

## Summary
Add optional Terms & Conditions to invoices. While creating or editing an invoice, admin can tick a checkbox to include fixed default Terms & Conditions in the invoice PDF. If unchecked, PDF stays as-is.

Placement: render Terms & Conditions after **Total Invoice Value (In Words)** and before **Payment Instruction**.

## Key Changes
- Add `include_terms_conditions` boolean column to `invoices`, default `false`.
- Add the field to `App\Models\Invoice::$fillable` and cast it to boolean.
- Validate `include_terms_conditions` as a nullable boolean.
- Persist checkbox value in invoice create and update flows.

## UI / PDF Changes
- Add an `Include Terms & Conditions in Invoice` checkbox to the invoice form, after Tax Summary and before Save Invoice.
- Show whether terms are included on the invoice detail page.
- Render a compact Terms & Conditions block in the invoice PDF only when enabled.

## Default Terms Text
1. Goods once sold will not be returned or exchanged unless agreed in writing.
2. Payment must be made as per the agreed payment terms.
3. Any dispute must be reported within 7 days from invoice date.
4. This invoice is subject to applicable local laws and jurisdiction.

## Test Plan
- Verify create stores `include_terms_conditions = true` when checked.
- Verify create/update stores `false` when unchecked.
- Verify edit can toggle terms on and off.
- Verify PDF shows terms only when enabled.
