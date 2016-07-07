# Codeception - module MailCatcher
This is a Codeception module to test the emails send by your application, using MailCatcher.

## Installation
To add it to your project run `composer require rezouce/codeception-mailcatcher`

You can then add *MailCatcher* to your Codeception configuration file in the modules enabled section:
`
modules:
    enabled:
        - MailCatcher
`

By default the module will try to contact MailCatcher on http://127.0.0.1:1080, you can change it using the configuration:
`
modules:
    enabled:
        - MailCatcher
    config:
        MailCatcher:
            url: 'http://127.0.0.1'
            port: '1080'
`

## Usage

### Assertions
```php
<?php
// Check if there is at least an email
$this->hasEmails();

// Count the number of mails
$this->seeNumberEmails(5);

// Check if at least an email contains a string in its source
$this->seeInEmail('A string.');
```

### Grabbing emails
```php
<?php
$emails = $this->getEmailsBySubject('subject');
$emails = $this->getEmailsBySender('user@example.com');
$emails = $this->getEmailsByRecipients(['user1@example.com']);
```
By default each of these methods will retrieve emails matching partially the given parameters.
You can use pass true as the second parameter to match strictly.

It's also possible to assert on the emails you grab:
```php
<?php
$emails = $this->getEmailsBySubject('subject');
$this->hasEmails($emails);
```

## License
This library is open-sourced software licensed under the MIT license
