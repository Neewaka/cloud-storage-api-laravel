
## cloud-storage-api-laravel
## API Routes

One or two sentence description of what endpoint does.

### Method title

> Version history note.

Description of the method.

```plaintext
METHOD /endpoint
```

Supported attributes:

| Attribute                | Type     | Required | Description           |
|:-------------------------|:---------|:---------|:----------------------|
| `attribute`              | datatype | Yes      | Detailed description. |
| `attribute` **(<tier>)** | datatype | No       | Detailed description. |
| `attribute`              | datatype | No       | Detailed description. |
| `attribute`              | datatype | No       | Detailed description. |

If successful, returns [`<status_code>`](../../api/index.md#status-codes) and the following
response attributes:

| Attribute                | Type     | Description           |
|:-------------------------|:---------|:----------------------|
| `attribute`              | datatype | Detailed description. |
| `attribute` **(<tier>)** | datatype | Detailed description. |

Example request:

```shell
curl --header "PRIVATE-TOKEN: <your_access_token>" "https://gitlab.example.com/api/v4/endpoint?parameters"
```

Example response:

```json
[
  {
  }
]
```


### Working with storage

You may add name of directory in {dirname} field to manage file located in this directory. 

+ `/file/{dirname?}` - `POST` - uploading a file.
+ `/file/{dirname?}/{name}` - `GET` - get file with specified name.
+ `/file/{dirname?}/{name}` - `PUT` - rename file with specified name.
+ `/file/{dirname?}/{name}` - `POST` - delete file with specified name.
