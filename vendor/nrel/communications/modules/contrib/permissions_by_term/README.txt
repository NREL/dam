Permissions by Term
====================================

DESCRIPTION
-----------
Restricts users from accessing the nodes related to specific taxonomy terms per
roles and users. Restriction also works for views, if teaser display mode is
used, as well as individual fields, like Title and Body.

Permissions by Term module additionally disallows users to select taxonomy
terms, for which they don't have access, on the node edit form.

WHY THIS MODULE WAS CREATED AND HOW?
------------------------------------
During work on a client project the Taxonomy Term Permissions module was
forked. It couldn't handle a different language and couldn't handle permissions
on a views page with listed taxonomy terms.

HOW TO SETUP THE MODULES FUNCTIONALITY ON YOUR DRUPAL-SITE?
-----------------------------------------------------------
1., Install the module.
2., Create a vocabulary and go to the taxonomy term add/edit-form for this
taxonomy term vocabulary. In the top of the form you can see the term
permissions. You can specify here, which roles and users can "use" this
taxonomy term either by node editing or accessing the nodes on a view or
on node display. The module hides nodes on a view.
3., After you have set permissions by term, they will take effect for the
related nodes on view and edit.
