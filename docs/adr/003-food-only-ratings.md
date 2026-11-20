# ADR 003 — Food-only ratings and Visit observations

Approved by the owner on 19 November 2026 in task 005. This decision supersedes the five-criteria Visit score and the Place mean of Visit scores.

Each ordered food or drink may have one holistic rating at 1.0–5.0 in half steps and an optional item review. The Visit score averages its rated items. Option A makes the Place score the average of individual rated item occurrences across published, non-deleted Visits at that active Place. An order recorded again on a later Visit counts again. For one Visit with `[5.0]` and another with `[3.0, 3.0, 3.0]`, the Place score is 3.5 across four rated items and two contributing Visits. Empty averages are null. Filtering and sorting use the unrounded score. Display uses one decimal.

Food filtering uses the matched item's own score. Visit and Place filters use their full food averages even when a food facet determines which results match. Recommendations and historical costs remain independent editorial and factual records.

Optional Visit `pros` and `cons` record non-food observations in the author's order, one per line. They render as escaped lists and qualify as narrative for publication. The Visit story, conclusion and item reviews retain their own fields.

The forward migration preserves dish ratings by renaming the column and retires the eight old Visit fields. It requires explicit operational approval when it detects legacy notes. The owner reviews any mapping privately after verifying a database and media backup. [Upgrade and recovery procedure](../operations.md#existing-databases-food-only-rating-upgrade).
