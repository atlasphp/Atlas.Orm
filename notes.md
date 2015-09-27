Lose the idea of injecting your own classes. This is a bound system, not for the domain but for persistence only. The idea specifically is that it is for DDD repos to pull from and send back to.  DDD entities and aggregates get mapped out of it and back into it.

Add checks on Row and Record types in the Table and Mapper.

The relationships may be reusing record objects, rather than building new ones. Is that a problem?

Have the ManyToMany load the through-relationship if it's NULL.

More tests for when relationships are missing.

Consider moving fetch*BySelect() into the level-specific Select classes. This means creating a MapperSelect class.

Consider creating an underlying GatewaySelect that talks directly to the database, without any table-specific stuff.

Empties ...

- NULL means "never attempted to fetch"
- FALSE means "failed to fetch a row/record"
- ARRAY means "failed to fetch a rowset/recordset"

... but then do we sometimes want a new Row/Record or empty RowSet/RecordSet? Especially in a relationship.

Consider a way to construct the Mapper with the Mapper Locator so it can fetch its own relateds.

Identity Field.

Complex primary keys.

Fetching strategies and identity lookups for compelex keys.

In RecordSets and Record relations, automatically set IdentityField when attaching.

* * *

What we're going for is "Domain Model composed of Persistence Model". That is, the Domain entities/aggregates use Records and RecordSets internally, but never expose them. They can manipulate the PM internally as much as they wish. E.g., an CustomerEntity might have "getAddress()" and read from the internal CustomerRecord. Alternatively, we can do "DDD on top of ORM" where repositories map the Records to Entities/Aggregates.

Now, when you change the values on the Entity. If you have two instances of a particular CustomerEntity, and you change values on the CustomerRow, it's now reflected across all instances of that particular CustomerEntity, because the CustomerRow is identity-mapped. Is that a problem?

* * *

In generator, allow for:

    --table={tablename}
    --primary={primarycol}
    --autoinc={bool}

That will allow specification of pertinent values.

That also means different templates for different classes.
