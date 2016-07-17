Schema Sample:

```
table_name: dinkly_sample_table
registry:
  - id
  - created_at
  - updated_at
  - dinkly_user_id: { type: int, allow_null: false, foreign_table: dinkly_user, foreign_field: id, foreign_delete: "SET NULL", foreign_update: "SET NULL" }
  - dinkly_group_id: { type: int, allow_null: false, foreign_table: dinkly_group, foreign_field: id }
  - login_count: { type: int, allow_null: false, default: 0 }
  - date_format: { type: varchar, length: 24, allow_null: true, default: "m/d/y" }
  - price: { type: decimal, length: "11,2", allow_null: true }
indexes:
 - created_at
 - created_at_update_at
   - created_at
   - updated_at
```