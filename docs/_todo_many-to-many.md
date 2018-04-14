Managing many-to-many relationships, e.g. tags through taggings.

- Adding a tag:

    ```
    // get all tags in the system
    $tags = $atlas->fetchRecordSet(Tags::MAPPER);

    // create new Tagging in memory
    $tagging = $thread->taggings->appendNew([
        'thread' => $thread,
        'tag' => $tags->getOneBy(['name' => $tag_name])
    ]);

    // persist the whole record with the tagging relateds,
    // which will insert the tagging
    $atlas->persist($tagging);
    ```

- Removing a tag

    ```
    // mark the Tagging association for deletion
    $thread
        ->taggings
        ->getOneBy(['tag_id' => $tag->id)
        ->setDelete();

    // persist the whole record with the tagging relateds,
    // which will delete the tagging and detach the related record
    $atlas->persist($thread);
    ```
