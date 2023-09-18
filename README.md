# HOUSE SNIFFER

A scrapper to track house market in Barcelona. With this scapper you can collect
houses of some market places in Barcelona. It is currently in early stage of
development, not thinked for profesional use.

This project will have basic configuration for [PHPUnit](https://github.com/sebastianbergmann/phpunit) and
[PHP-CS-Fixer](https://github.com/friendsofphp/php-cs-fixer) libraries, configured `composer.json`
with PSR-4 namespaces for source code and tests, `.gitignore` with basic files and directories to exclude them from Git, changelog file
and README with cool  badges :)

[![License badge](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
![Code Size](https://img.shields.io/github/languages/code-size/martiadrogue/housesniffer)
![Lines of code](https://img.shields.io/tokei/lines/github/martiadrogue/housesniffer)

## Installation

For scrapping houses from internet  just execute the following command

```bash
$ git clone <url_to_the_project>
```
## Usage

For scrapping houses from internet  just execute the following command

```bash
$ bin/console app:cave-hunter <name_of_target>
```
> NOTE: You can add `--vvv`  if you want to see all feedback

## TODO

List of feeds to scrap

 - https://www.yaencontre.com/alquiler/pisos/barcelona
   https://api.yaencontre.com/v3/search?family=FLAT&lang=es&location=barcelona&operation=RENT&pageNumber=5&pageSize=42
 - https://www.zillow.com/new-york-ny/

Next Steps

 - add session
 - share params between hints
 - create composed data from more than one field
 - rethink mutations
 - use Entities for hints
 - allow webkit from command
 - allow proxy from command
 - store incomplete data to another place
 - store locator with parsed data
 - validate parsed data
 - fetch pdp of incomplete data

## Style Rules

 - Use yoda notation
 - Use the prefix `List` for simple arrays
 - Use the prefix `Map` for key value arrays
 - Composition over inheritance
 - Declarative over imperative
 - Data hiding
 - Use the verb `mutate` on methods that update more than one property
 - Use the verb `set` on methods that update one property
 - Use the verb `get` on methods that get a property
 - Use the verb `is` or `has` on methods that return boolens
 - Use the verb `seek` on methods that look for something and the return isn't granted
 - Use the name `style` in the package that contains behaviours for the design pattern

## NOTES

 - Goal is to discover new items, and have te current ones up to date
 - Each site works differently (idealista filter `recientes` means last ads,
 while `relevancia` means one ad new one ad updated)
 - Not all parts of the information is available in the feed
 - There are 2 updates, Normal updates and Extraordinary updates
    - Apexs update involves those fields that get updated frequently like the
    prices
    - Complentary update involves those fields that are not available in the
    main feed but are required to give meaing to the item
 - There are 3 tipes of information: Critical, Complentary and Redundant
    - Cirital data is everything that helps identify the item
    - Complentary data is everything that contains details
    - Redundant data is everything that can be considered overload or isn't directly
    reltated with the item

steps app
 - get hints
 - request data
 - parse data
 - persist data

steps hints
 - parse hints
 - validate hints
 - mutate hints

steps request
 - start session
 - call request
 - return data
 - check for more requests
 - do it again

steps parser
 - get data
 - get hints
 - parse data

steps persisting
 - save data
 - validate data
 - move data

## License

This project is released under the MIT license [license](LICENSE).

Copyright (c) 2023, Marti Adrougue
