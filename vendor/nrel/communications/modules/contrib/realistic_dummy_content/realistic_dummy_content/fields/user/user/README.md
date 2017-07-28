The files herein are used by Realistic dummy content to replace generated
content for fields in user entities.

The file path is realistic_dummy_content/fields/user/user:

 * realistic_dummy_content/fields: because all files used to replace fields are
   in realistic_dummy_content/fields
 * user: this is the entity type
 * user: this is the bundle (all entities must have an entity type and a
   bundle; in the case of "user", this requirement means that the bundle and
   the entity type have the same name: user).

About the symbolic link from "picture" to "user_picture": In Drupal 7, the user
picture is stored in a "picture" attribute; in Drupal 8, it is in a
"user_picture" field. To make sure Realistic Dummy Content works on both
frameworks, we have added a symbolic link from one to another. For more on
symbolic links see ./realistic_dummy_content/fields/node/README.txt.
