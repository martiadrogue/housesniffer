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

 - https://www.tucasa.com/compra-venta/viviendas/barcelona/barcelona/?r=&idz=0008.0001.9999.0001
 - https://www.yaencontre.com/alquiler/pisos/barcelona
 - https://www.pisos.com/alquiler/pisos-barcelona_capital/fecharecientedesde-desc/
 - https://www.fotocasa.es/es/comprar/viviendas/barcelona-capital/todas-las-zonas/l
   https://web.gw.fotocasa.es/v2/propertysearch/search?combinedLocationIds=724,9,8,232,376,8019,0,0,0&culture=es-ES&includePurchaseTypeFacets=true&isMap=false&isNewConstructionPromotions=false&latitude=41.3854&longitude=2.17754&pageNumber=3&platformId=1&propertyTypeId=2&sortOrderDesc=false&sortType=price&transactionTypeId=1
 - https://www.zillow.com/new-york-ny/

Next Steps

 - allow custom headers from command
 - allow delay time from command
 - allow proxy from command
 - store incomplete data to another place
 - fetch pdp of incomplete data
 - group delay, headers and proxy in a same object of request toolkit
 - reword app base on observer pattern, event driven

## NOTES

Goal is to discover new items, and have te current ones up to date
Each site works differently (idealista filter recientes means last ads, while
relevancia means one ad new one ad updated)
Not all information is available in the feed
There are 2 tipes of information: critical and complimentary
Cirital data are ups and downs of price, it's open or sell
complentary data that are important but don't change. So, there isn't an
inminent necessity to have it, and having this data up to date is less important than anything

steps app
 - vaildate instructions
 - request data
 - parse data

steps instructions
 - parse instructions
 - validate instructions
 - mutate instructions

steps request
 - call request
 - return data
 - check for more requests
 - do it again

steps for parsing data
 - search keys
 - parse data

## License

This project is released under the MIT license [license](LICENSE).

Copyright (c) 2023, Marti Adrougue
