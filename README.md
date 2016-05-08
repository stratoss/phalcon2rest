Phalcon2Rest
===========

A base project for APIs using the [Phalcon2][phalcon] framework
---------------------------------------------------

This project is a fork of [cmoore4][cmoore4]'s PhalconRest, but modified to work correctly with Phalcon2.
It is including latest phpleague's [OAuth2][OAuth2] at the moment (5.x) and using Json Web Tokens (JWT).
Rate limiting is implemented as well.

The Phalcon framework is an awesome PHP framework that exists as a C-extension to the language.
This allows it to be incredibly fast.  But aside from its quickness, it is an amazingly
powerful fraemwork with excellent [documentation][phalconDocs] that follows many best practises of
modern software development.  This includes using the Direct Injection pattern to handle service
resolution across classes, a PSR-0 compliant autoloader, MVC architecture (or not), caching
handlers for database, flatfile, redis, etc.. and a ton of additional features.

The purpose of this project is to establish a base project with Phalcon that uses the best practices
from the Phalcon Framework to implement best practises of [API Design][apigeeBook].

Writing routes that respond with JSON is easy in any of the major frameworks.  What I've done here is to
go beyond that and extend the framework such that APIs written using this project are pragmatically
REST-ish and have conveniance methods and patterns implemented that are more than a simple
'echo json_encode($array)'.

Provided are robust Error messages, controllers that parse searching strings and partial responsese,
response classes for sending multiple MIME types based on the request, and examples of how to implement
authentication in a few ways, as well as a few tempaltes for implementing common REST-ish tasks.

It is highly recommended to read through the index.php, HTTPException.php and RESTController.php files, as
I've tried to comment them extensively.


API Assumptions
---------------

**URL Structure**

```
/v1/path1/path2?q=(search1:value1,search2:value2)&fields=(field1,field2,field3)&limit=10&offest=20&type=csv&suppress_error_codes=true
```

**Request Bodies**

Request bodies will be submitted as valid JSON.

The Fields
-----------

**Search**

Searches are determined by the 'q' parameter.  Following that is a parenthesis enclosed list of key:value pairs, separated by commas.

> ex: q=(name:Jonhson,city:Oklahoma)

**Partial Responses**

Partial responses are used to only return certain explicit fields from a record. They are determined by the 'fields' paramter, which is a list of field names separated by commas, enclosed in parenthesis.

> ex: fields=(id,name,location)

**Limit and Offset**

Often used to paginate large result sets.  Offset is the record to start from, and limit is the number of records to return.

> ex: limit=20&offset=20   will return results 21 to 40

**Return Type**

Overrides any accept headers.  JSON is assumed otherwise.  Return type handler must be implemented.

> ex: type=csv

**Suppressed Error Codes**

Some clients require all responses to be a 200 (Flash, for example), even if there was an application error.
With this paramter included, the application will always return a 200 response code, and clients will be
responsible for checking the response body to ensure a valid response.

> ex: suppress_error_codes=true

Installation
------------
**Getting composer**
```
curl -sS getcomposer.org/installer | php
```

**Installing the project & dependencies (expecting phalcon2 to be loaded as a module!)**
```
php composer.phar create-project stratoss/phalcon2rest MyAPI --stability dev --no-interaction
```

**Public / Private keys used for JWT signing**
Sample keys are generated in the `ssl` folder, you must regenerate your own set before going to production!

```
openssl genrsa -out private.key 1024
openssl rsa -in private.key -pubout -out public.key
```

Responses
---------

**Retrieving access token using password grant**

```
curl https://domain/v1/access_token -X POST --data "grant_type=password&client_id=1&client_secret=pass2&username=stan&password=pass&scope=basic"
```
```
{
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImUyY2ExYjhiM2ZlODRhM2Q2ZGFmZjZhNmFiZmQwMjJiNzQxMGYxOWNmODAzMWVkOThlNjc4YzVjN2Q3Mjc4MTk0NDFhYzMyNTc5OTM1OTIxIn0.eyJhdWQiOiIxIiwianRpIjoiZTJjYTFiOGIzZmU4NGEzZDZkYWZmNmE2YWJmZDAyMmI3NDEwZjE5Y2Y4MDMxZWQ5OGU2NzhjNWM3ZDcyNzgxOTQ0MWFjMzI1Nzk5MzU5MjEiLCJpYXQiOjE0NjI3MjkxMTgsIm5iZiI6MTQ2MjcyOTExOCwiZXhwIjoxNDYyNzMyNzE4LCJzdWIiOiIxIiwic2NvcGVzIjpbImJhc2ljIl19.VtKS7k1WiebSHiYwSrYJ9D8G90BQE81UkCNr8RI3-Ul3XaUw8kF1mMR-Q7YeO4pISUT48Gu5Sj6fTSsqF1n0Qz3axR-qcJstSwy_T0VZDNQYYdGGoXSRWabiIkLA50lbaPd8YGPLZO3IijgvyZxC3Miz7iUxxz1XOHvzHiDZP5s",
    "refreshToken": "U8PSWA\/xs\/Qw7\/\/Do76KM61+R6tUQwoex4YmnH0HjUCjzDKR22mRvSSPzkgbuR6EqjaaAp\/QWdBcYjgZj5XOyXAH9LM7C\/uBr4YeLUeTvzf7SylqIlLOuKe6aOZzD+L5ztSNdMCzjCQ1PZSWtk+hm7Ik1lUfXe1A\/09qI+hfG+p5cXHvow26ZSWz42uYaoEV5MAh+VBvR2vWx4WrAa6JJ\/6QISJRu+KTUXJmiGBbzhZiHZJ7+pyTeLiFk\/euT8aTa2tIz5WMogQDhMfzzPadOZY88jrc9mPuwxOv8DPKnysAlYHklnEQzESoP4MmKP0D9yrbLx80eE3PdEnj1hPYcpLIb8Dvc1IK+rIzg9+71HSW5XH54npY7MlLbNPDAB02bdX5SDWJhT8XwN8zqZQiLOUwBgP\/ESjW5dI7ckLzmmguqRPbd3TtNoYGultyFUCIxKH1FVNqJrxgVqJk083KXqFAzcsZw6cxgvies3djGxcPGCzyNRlxuEU8+ZoMJ0u0"
}
```

**Retrieving access token using client_credentials grant**

```
curl https://domain/v1/access_token -X POST --data "grant_type=client_credentials&client_id=1&client_secret=pass2&scope=basic"
```

```
{
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQwYjU1YjJiMjhhYmIxOTMwODk3OTg2ZTIxN2RkMTkwZGY1YTlkYzk2NGJmZjljY2ZjMWQ0NGI4Y2RiNjU0OTAyYjMwM2M1ZDliMzY5ZWQxIn0.eyJhdWQiOiIxIiwianRpIjoiZDBiNTViMmIyOGFiYjE5MzA4OTc5ODZlMjE3ZGQxOTBkZjVhOWRjOTY0YmZmOWNjZmMxZDQ0YjhjZGI2NTQ5MDJiMzAzYzVkOWIzNjllZDEiLCJpYXQiOjE0NjI3MjE4NzcsIm5iZiI6MTQ2MjcyMTg3NywiZXhwIjoxNDYyNzI1NDc3LCJzdWIiOiIiLCJzY29wZXMiOlsiYmFzaWMiXX0.KDkaVBMBX4UelYJ4UoknjgrssEaqpQPj2MPe4ArIppIc0BYNA-5xxVWCSu8rSGKO7QAVM2XSyiux3yq8NoClgtaPlPtpZN6pcSfwGo9MSM6IwQanpd978pwPCi-tXXl4mlViph9sgxPioJ3CzCBoJTpeEtRnEm6nxMUgLnncXps"
}
```

**Exchanging refresh token for a new set of refresh token + access token**
```
curl https://domain/v1/access_token -X POST --data "client_id=1&client_secret=pass2&grant_type=refresh_token&scope=basic&refresh_token=YOUR_REFRESH_TOKEN"

{
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImY0NGRiMzQ1MzE0MDdlMWY2MWU0N2NkODQ2ZjIxOTRiMjNiZWZhNzZmOWVjYWY5ZDIyMWFhYTg5MTVhMDhjOGFhMzkzYTdmMGI4NGEwNjQ1In0.eyJhdWQiOiIxIiwianRpIjoiZjQ0ZGIzNDUzMTQwN2UxZjYxZTQ3Y2Q4NDZmMjE5NGIyM2JlZmE3NmY5ZWNhZjlkMjIxYWFhODkxNWEwOGM4YWEzOTNhN2YwYjg0YTA2NDUiLCJpYXQiOjE0NjI3MjIzMjIsIm5iZiI6MTQ2MjcyMjMyMiwiZXhwIjoxNDYyNzI1OTIyLCJzdWIiOiIxIiwic2NvcGVzIjpbImJhc2ljIl19.COJ5kAWEEjZyKN_k1N0sgiLJzpEtlgT9H3oJpeicQ-bZteuABZ3sYWCgBY2FrRm6Q8ouMra9WXj38NnwYRgOusRq2H1JL-3redvTu8LitPljNLYritSAuPivOVY4e6FVjQHeuXfIl37rmKIHUXmJcUSJRh1XOqW9mXJGggiXhlI",
    "refreshToken": "muYBWN8fSzSL2UCQU0FCq7EZrJ7bPBmJxLsHOTzBSoHJn0gT+ilWyeJzvOqrlVJel4V8K7HIOQfExbKB5l0UrwzFo5UDCz5qj72wWgUn8aJWY09LfGZAs6Qsx\/INLmg6y7petQdtWspAPWlaid8OBk2w5IsqQ7kLFATHCA9fWIg3HWRrc8RPkWeBgOZ5ekRO1dGnmzDm+HLmt8hvIq7uiNDRINYYDwmYh50Ifkv8iJhxL7Pj351KPg43G9pB6L8mNfVizx71c3cofuHlTYMc4S5pt9PFBg7kbtR+qYAD5Wpm3jK204HTpx\/lYyVtEZuFou8O+7ssWlWCSXf7wogxPy9fMuRgXzONnqUn8XHDJEBOxZIIVeu7AAgsWKGJvNrLVY+81oa8BQL1MdCqxQs8vVnHgzq9+bnrjZPlhcvm\/jhWzeCx6X\/fjdneTsZXOZXLK0OpCYNkyOaT2xC5H3RI2+jRGU0HCXGJTmuBlz4Kx48fdUuy2DwF\/DL+LS2mWE6o"
}
```

All route controllers must return an array.  This array is used to create the response object.

**JSON**

JSON is the default response type.  It comes with an envelope wrapper, so responses will look like this:

```
curl "https://domain/v1/example?q=(id:3)&fields=(author,title,year)" -H "Authorization: Bearer YOUR_ACCESS_TOKEN"

[
    {
        "author": "Stanimir Stoyanov",
        "title": "OAuth2 with Phalcon",
        "year": "2016"
    }
]

curl "https://domain/v1/example?q=(year:2010&fields=(author,title)" -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
[
    {
        "author": "John Doe",
        "title": "Greatest book"
    },
    {
        "author": "John Doe",
        "title": "Book of books"
    }
]
```

The envelope can be suppressed for responses via the 'envelope=false' query paramter.  This will return just the record set by itself as the body, and the meta information via X- headers.

Often times, database field names are snake_cased.  However, when working with an API, developers
generally prefer JSON fields to be returned in camelCase (many API requests are from browsers, in JS).
This project will by default convert all keys in a records response from snake_case to camelCase.

This can be turned off for your API by setting the JSONResponse's function "convertSnakeCase(false)".

**CSV**

CSV is the other implemented handler.  It uses the first record's keys as the header row, and then creates a csv from each row in the array.  The header row can be toggled off for responses.

```
curl "https://domain/v1/example?q=(year:2010)&fields=(id,author,title)&type=csv" -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
id,author,title
1,"John Doe","Greatest book"
2,"John Doe","Book of books"
```

Errors
-------

Phalcon2Rest\Exceptions\HttpException extends PHP's native exceptions.  Throwing this type of exception
returns a nicely formatted JSON response to the client.

```
throw new \Phalcon2Rest\Exceptions\HttpException(
	'Could not return results in specified format',
	403,
	null
	array(
		'dev' => 'Could not understand type specified by type paramter in query string.',
		'internalCode' => 'NF1000',
		'more' => 'Type may not be implemented. Choose either "csv" or "json"'
	)
);
```

Returns this:

```
{
     "devMessage": "Could not understand type specified by type paramter in query string.",
     "error": 403,
     "errorCode": "NF1000",
     "more": "Type may not be implemented. Choose either \"csv\" or \"json\"",
     "userMessage": "Could not return results in specified format"
}
```


Example Controller
-------------------

The Example Controller sets up a route at /example and implements all of the above query parameters.
You can mix and match any of these queries:

>  api.example.local/v1/example?q=(author:Stanimir Stoyanov)

>  api.example.local/v1/example?fields=(id,title)

>  api.example.local/v1/example/1?fields=(author)&envelope=false

>  api.example.local/v1/example?type=csv

>  api.example.local/v1/example?q=(year:2010)&offset=1&limit=2&type=csv&fields=(id,author)

Rate Limiting
--------------

There are 3 rate limiters implemented, configured in `config/config.ini`

> How many request for access token are permitted

[access_token_limits]

r1 = 5

means 1 request per 5 seconds

> How many unauthorized requests

[api_unauthorized_limits]

r10 = 60

means 10 requests per 1 minute

> Everything else

[api_common_limits]

r600 = 3600

means 600 requests per hour

**Tracking the rate limiter**

Each requests returns the X-Rate-Limit-* headers, e.g.

```
HTTP/1.1 200 OK

X-Rate-Limit-Limit: 600
X-Rate-Limit-Remaining: 599
X-Rate-Limit-Reset: 3600
X-Record-Count: 2
X-Status: SUCCESS
E-Tag: 6385b20e0a8a3fb0edd588d630573f00
```

When the limit is reached:
```
< HTTP/1.1 429 Unknown Status Code
< X-Rate-Limit-Limit: 600
< X-Rate-Limit-Remaining: 0
< X-Rate-Limit-Reset: 3355
< X-Status: ERROR
< E-Tag: f22e9815ad32e143287944e727627e9c
<

{
    "errorCode": 429,
    "userMessage": "Too Many Requests",
    "devMessage": "You have reached your limit. Please try again after 3355 seconds.",
    "more": "",
    "applicationCode": "P1010"
}
```

[phalcon]: http://phalconphp.com/index
[phalconDocs]: http://docs.phalconphp.com/en/latest/
[apigeeBook]: https://blog.apigee.com/detail/announcement_new_ebook_on_web_api_design
[OAuth2]: https://github.com/thephpleague/oauth2-server
[cmoore4]: https://github.com/cmoore4/phalcon-rest/