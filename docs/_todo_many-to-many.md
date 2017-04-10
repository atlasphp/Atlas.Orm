Managing many-to-many relationships, e.g. tags through taggings.

- Adding a tag:

    ```
    // get from a pre-fetched RecordSet of tags
    $tag = $tags->getOneBy(['name' => $tag_name]);

    // add Tag to in-memory Record
    $post->tags[] = $tag;

    // create new Tagging in memory and set columns on row
    $tagging = $post->taggings->appendNew([
        'post_id' => $post->id,
        'tag_id' => $tag->id
    ]);

    // plan to insert the new Tagging
    $transaction->insert($tagging);
    ```

- Removing a tag

    ```
    // remove from in-memory RecordSet
    $tag = $post->tags->removeOneBy(['name' => $tag_name]);

    // remove from in-memory RecordSet
    $tagging = $post->taggings->removeOneBy(['tag_id' => $tag->id);

    // plan to delete the Tagging
    $transaction->delete($tagging);
    ```
