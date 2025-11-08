# Question2Answer A bunch of miscellaneous settings plugin

1. Reordering Title and Suggested Questions on the Ask Page

The “Ask a Question” page now displays the question title field and the suggested questions list after the discreption field. This change improves the user flow by encouraging visitors to type their entire question first and then type a suitable title.

2. Adding Lists Link to the Favorites Page (Questions Block)

On the “My Favorites” page, the Questions block now includes a “Lists” redirection link. This allows users to quickly access their saved lists directly from their favorites area for easier navigation.

3. Hiding User Profiles for Low-Activity Users

User profiles (including activity, wall, and related pages) are now hidden from all regular users if the profile owner does not meet the minimum configured activity thresholds:

Active days less than the configured limit

Approved posts (Questions, Answers, or Comments) less than 2 ( 2 is hardcoded in the qa-user-profile-hide-layer.php file. )

Exceptions: Moderators, admins, and super admins can still view all profiles.


4. Restricting Profile Editing for Low-Activity Users

The “Edit Profile” button and My Account link are automatically hidden for users who have:

Active days less than the configured limit, or

Approved posts (Questions, Answers, or Comments) less than 2 ( 2 is hardcoded in the qa-user-profile-hide-layer.php file. )

This feature temporarily restricts new or inactive users from editing their profiles until they reach the configured activity thresholds (approved posts or active days), helping to deter spam or abusive accounts.

5. Print Option for Questions and Blogs

- Adds a **Print** button on the **Question** and **Blog** view pages.  
- The **Print button** can be **enabled or disabled** from the **Admin Panel → Misc Tweaks** section.  
- The **print layout style** can be customized directly from the same admin page. 

6. Hide / Show Side Panel
Adds a widget **Hide Side Panel** button to hide the side panel.
When hidden, the main content expands to full width.
The show side panel button is draggable, remembers its position (from browser memory), and auto-adjusts on window resize.

7. Limited Username Change

Adds per-user control over username changes.
Admins can configure how many times a user may change their username (0, 1, 2, etc.).
If set to 0, usernames are locked completely.