The question was, "How do you limit the *related* sets in query-and-stitch?"

Answer: it's a bit trickier; you have to build a UNION query.

SELECT * FROM comments WHERE post_id = 1 LIMIT 10
UNION ALL
SELECT * FROM comments WHERE post_id = 2 LIMIT 10
UNION ALL
etc

Then you can loop through and stitch them into the posts.

Again, this is a case where having control over the SQL is immensely useful.
