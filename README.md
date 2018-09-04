# webhook-logger

A web-app to help debug/log webhook requests.

- Will return a printed version of the response. Add the following query param: `format=var_dump` to use `var_dump()` instead of `print_r()`. For non-GET requests, will return a json output.
- Can log the request parameters by adding query param: `log=1`  (logs are truncated at 1M)
