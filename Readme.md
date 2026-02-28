# Lazarusphp Session Manager

#### Index

- [Whats new](#whats-new)
- [What is the Session manager](#what-is-the-session-manager)
- [Installing the Session Manageer](#installing-sessions-manager)
- [The Basics](#the-basics)
- [Assigning Sessions Values](#assigning-sessions-values)
- [Retrieving Session Values](#retrieving-session-values)
- [Overriding Configuration Data](#overriding-configuration-data)
- [The Session Writer Class](#the-session-writer-class)
- [Extra Session Options](#extra-session-options)
- [Enabling Session Manager for Standalone Mode](#enabling-session-manager-for-standalone-mode)
- [Custom Writer Requirements](#custom-writer-requirements)
- [Final Recommendation](#final-recommendation)

- [Deleting Sessions](#deleting-sessions)
- [Garabage Collection](#garbage-collection)

## Current Version

Version 2.0.

## Whats new?

- New Code Restructure
- New Instantiation Method
- New implementation for standalone methods
- optional Replacable Config Options.

## What is the session manager.

The Session manager is a Database driven Session handler, storing all session data within a database allowing for more control.

## Installing Sessions Manager

```
composer require lazarusphp/sessionmanager
```

## The Basics

the Session manager can be called at anytime within a website, however a first time instantiation to connect to a database is required.

### Instantiation

Session manager can now work in both an integrated mode or as a standalone script.

**Integrated Mode**

Integrated mode is designed to work with LazaruasPhp Database and QueryBuilder, Although this can be combined with a standalone script, this methid relies on other scripts designed by lazarusphp.

**Standalone Mode**

Standalone Mode gives you the ability to overide the SessionWriter class with your own custom file, this gives the ablity to pass custom database instructions. to the required methods.

```php
use LazarusPhp\SessionsManager\Sessions;
Sessions::create()->save();
```

upon instantiation of the class a staic instance will be created this allows the session to run on a singleton

## Assigning Sessions Values

```php
$sessions = Session::create();
$session->username = "mike";
$session->id = 1;
```

## Retrieveing Session Values.

```php
$sessions = Sessions::create();
echo $sessions->username;

echo $sessions->id;
```

## Overriding Configuration data

As of Version Session Manager 2.0 it is now possible to override all Configuration Values.

by default these values are as follows.

```php

    [
        "days" => 7, // Days of how long the session will last
        "path"=>"/", // Path of the Session.
        "table" => "sessions", //Name of the Sessions table in the database.
        "name" => "sessions", // Session name by default it is sessions.
        "domain" => isset($_SERVER['HTTP_HOST']) ? '.' . $_SERVER['HTTP_HOST'] : '' // which domain is allowed to access this by default is set to a wildcard of the called domain name,
        "secure" => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), // define if the session is to only be used on https
        "httponly" => false, // define if the session can only be used on http;
        "samesite" => "lax", // can be set to strict or lax defaulted to lax
    ];

```

Overriding config files is done on instantiation of the session and is chained like so.

```php

Sessions::create()->withConfig([
        "days" => 8,
        "path"=>"/",
        "table" => "sessions",
        "name" => "sessions",
])->save();

```

any values not edited in the withConfig method will revert to the default value.

**Note about Defaults**
By default days and table are already applied and are defaulted adding these key pairs will overwrite default values.

### The Session Writer class.

The Session Writer class is a custom class designed to hold Database query requests for the open, close, read, write, destroy and gc methods. these methods are a requirement as they implemenet the SessionHandlerInterface interface.

## Extra Session Options

### Deleting Sessions

the Session manager has the ability to remove individual Sessions or can remove them all as a whole by using the deleteSessions() method;

```php
use Lazarusphp\SessionsManager\Sessions;

$sessions = Sessions::create();
// Delete Specific Sessions
$session->deleteSessions("username","email","password");

// Delete All Sessions
$sessions->deleteSessions();
```

Be Aware that deleting all Sessions (Not Specifying Sessions) Will do a session_destroy() call and will also delete the entire session from the database whereas choosing the sessions will only remove the specific values from the database, keeping the session intact. this will override anything currently set by the server.

### Garbage Collection

Garbage collection is a built in function from php and is used to delete Generated Sessions which have expired, this can be done by using the session_gc function

```php
session_gc();
```

once called the script will go through all the sessions and will delete any stale records which hold an expiry set before the current timestamp.
This can be triggered using the `session_gc()` function:

```php
session_gc();
```

The function iterates through all sessions and removes expired records where the expiration timestamp is earlier than the current time.

## Enabling Session Manager for standalone mode.

Similar to overriding config files, you can override the SessionWriter class with a custom implementation. This allows you to connect to a dedicated database and use custom database queries for session management. Configure this at instantiation time like so:

```php
use App/Writers/SessionWriters/CustomWriter;
Sessions::create()->withWriter(CustomWriter::class)->save();
```

## Custom Writer Requirements

Using a custom writer must adhere to the rules imposed by `SessionHandlerInterface` and LazarusPhp `SessionInterface` files.

### SessionInterface Requirements and Recommendations

When implementing a custom writer, your class must implement the `SessionHandlerInterface` and follow the `SessionInterface` specifications. This ensures compatibility with the Session Manager.

Required methods:

- `open(string $path, string $name): bool`
- `close(): bool`
- `read(string $id): string|false`
- `write(string $id, string $data): bool`
- `destroy(string $id): bool`
- `gc(int $max_lifetime): int|false`

For more information, see [SessionHandlerInterface documentation](https://www.php.net/manual/en/class.sessionhandlerinterface.php).

**passConfig(array $config):voic**

```php
public function passConfig(array $config):void
{
    $this->config = $config;
}

```

[click here](https://www.php.net/manual/en/class.sessionhandlerinterface.php) : for information on SessionsHandlerInteface

## Final Recommendation

As part of the LazarusPhp framework, Session Manager implements a helper function called `session()`. This function provides convenient access to the Sessions class. You can use it for both initialization and accessing session data throughout your application.

```php

function session():SessionManager
{
    return Sessions::create();
}
// Access can then be done like so.

session()->username = "test";

echo session()->username;
```
