Managing many-to-many relationships, e.g. tags through taggings.

- Adding a tag:

    ```
    // get from a pre-fetched RecordSet of tags
    $tag = $tags->getOneBy(['name' => $tag_name]);

    // create new Tagging in memory on the "through" related
    $tagging = $thread->taggings->appendNew([
        'thread' => $thread,
        'tag' => $tag
    ]);

    // if you fetched many-to-many, add Tag to in-memory Record
    $thread->tags[] = $tag;

    // persist the whole thread and its relateds
    $atlas->persist($thread);

    // ... or insert the Tagging by itself ...
    $atlas->insert($tagging);

    // ... or plan to insert the new Tagging in a transaction
    $transaction->insert($tagging);
    ```

- Removing a tag

    ```
    // remove the Tagging from the "through" related
    $tagging = $thread->taggings->removeOneBy(['tag_id' => $tag->id);

    // if you fetched many-to-many, also remove from in-memory RecordSet
    $tag = $thread->tags->removeOneBy(['name' => $tag_name]);

    // mark the tagging for deletion and persist the whole thread ...
    $tagging->markForDeletion();
    $atlas->persist($thread);

    // ... or delete it by itself ...
    $atlas->delete($tagging);

    // ... or plan to delete the Tagging in a transaction
    $transaction->delete($tagging);
    ```
