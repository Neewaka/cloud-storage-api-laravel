
## API Routes

### Authentication

+ `/api/auth/register` - `GET` - user registers on platform and gets token for using storage.

Supported attributes:

| Attribute                | Type     | Required |
|:-------------------------|:---------|:---------|
| `name`                   | string  | Yes      | 
| `email`              | string | Yes       | 
| `password`              | string | Yes       | 

+ `/api/auth/login` - `GET` - user logs in on platform and gets token for using storage.

Supported attributes:

| Attribute                | Type     | Required |
|:-------------------------|:---------|:---------|
| `email`              | string | Yes       | 
| `password`              | string | Yes       | 


+ `/api/auth/logout` - `GET` - user logs in on platform and gets token for using storage.

### Working with storage

You may add name of directory in {dirname} field to manage file located in this directory. 


+ `/api/file/{dirname?}` - `POST` - `uploading a file.`

Supported attributes:

| Attribute                | Type     | Required |
|:-------------------------|:---------|:---------|
| `file`                   | file     | Yes      | 
| `expires_in`             | int      | No       | 

+ `/api/file/{dirname?}/{name}` - `GET` - get file with specified name.
+ `/api/file/{dirname?}/{name}` - `PUT` - rename file with specified name.
+ `/api/file/{dirname?}/{name}` - `POST` - delete file with specified name.
+ `/api/file/publish/{dirname?}/{name}` - `POST` - publish file with specified name and get public link on file which can be used instead of attribute `name` in `GET` method of file.
+ `/file/list` - `GET` - get list of all files contained in user storage.

### Working with directories

+ `/api/directory/create` - `POST` - create new directory in user storage.

Supported attributes:

| Attribute                | Type     | Required |
|:-------------------------|:---------|:---------|
| `dirname`                   | string     | Yes      | 

+ `/api/directory/size/{dirname?}` - `GET` - get size of storage when no `dirname` attribute given otherwise get size of specifed directory.

### User information

+ `/api/user/info` - `GET` - get basic information about current user.







