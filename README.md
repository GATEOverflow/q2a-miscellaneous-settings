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