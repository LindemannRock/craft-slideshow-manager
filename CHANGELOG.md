# Changelog

## [5.8.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.7.0...v5.8.0) (2026-02-26)


### Features

* **SlideshowManager:** add support for named CSS style presets ([e04160b](https://github.com/LindemannRock/craft-slideshow-manager/commit/e04160b9acd2a8f62a2b8f5ca645d1d4e7b66437))

## [5.7.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.6.3...v5.7.0) (2026-02-26)


### Features

* **SettingsController:** add styles settings tab and update validation ([552315a](https://github.com/LindemannRock/craft-slideshow-manager/commit/552315a7a4dd4e3d899e143f5998dc9d6f86b11d))

## [5.6.3](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.6.2...v5.6.3) (2026-02-23)


### Bug Fixes

* **SettingsController:** validate and sanitize settings section parameter ([e882a85](https://github.com/LindemannRock/craft-slideshow-manager/commit/e882a85e231334628a638e37382e7d505418b5cc))
* **SlideshowManager:** add no-op setSettings method for clarity ([b064056](https://github.com/LindemannRock/craft-slideshow-manager/commit/b0640569ec23f614ffb4498ee949e24ed974d1d0))
* **SlideshowManager:** update permission heading to use settings name ([a8d175b](https://github.com/LindemannRock/craft-slideshow-manager/commit/a8d175bf28d625fd6ef8d7a95f326ddcf2a7d767))


### Miscellaneous Chores

* add .gitattributes with export-ignore for Packagist distribution ([d951743](https://github.com/LindemannRock/craft-slideshow-manager/commit/d951743ed59e7381fe511fba2c75cec96d3c6281))
* switch to Craft License for commercial release ([6a3ce04](https://github.com/LindemannRock/craft-slideshow-manager/commit/6a3ce04e7747a104fdd7ea3e87559cd0cad37e0c))

## [5.6.2](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.6.1...v5.6.2) (2026-02-05)


### Bug Fixes

* **SlideshowManager:** update [@since](https://github.com/since) version in getCpSections method ([742f7c2](https://github.com/LindemannRock/craft-slideshow-manager/commit/742f7c2316442d81f84532c06f5c50971d5725e8))

## [5.6.1](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.6.0...v5.6.1) (2026-01-21)


### Bug Fixes

* update header to reflect correct section title in general settings ([35b939e](https://github.com/LindemannRock/craft-slideshow-manager/commit/35b939ebb5bc6f16e553b29728a98db14dc95e06))

## [5.6.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.5.2...v5.6.0) (2026-01-18)


### Features

* enhance SlideshowManager initialization and add searchable attributes to Slide element ([b1f679b](https://github.com/LindemannRock/craft-slideshow-manager/commit/b1f679b625fd01e9aaefb4a9bb543e3162d836a0))

## [5.5.2](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.5.1...v5.5.2) (2026-01-11)


### Bug Fixes

* update pluginName property to be non-nullable with a default value ([2f57e18](https://github.com/LindemannRock/craft-slideshow-manager/commit/2f57e18ae7f5bc8b625502e5dfba68f40e6cde40))

## [5.5.1](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.5.0...v5.5.1) (2026-01-11)


### Bug Fixes

* change pluginName property type from nullable to string ([0ed5733](https://github.com/LindemannRock/craft-slideshow-manager/commit/0ed573365b6262b3aa6e8cb974d24dc2c7519291))
* update pluginName to use getFullName method for better accuracy ([fc63658](https://github.com/LindemannRock/craft-slideshow-manager/commit/fc63658df1d438968aeebca64cf7a40b372e2d30))

## [5.5.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.4.0...v5.5.0) (2026-01-08)


### Features

* Migrate to shared base plugin (lindemannrock/craft-plugin-base) ([d237151](https://github.com/LindemannRock/craft-slideshow-manager/commit/d237151412f2c2672938d0b8d5310d3c9d3ecbfa))


### Bug Fixes

* update success message for settings save confirmation ([318efc3](https://github.com/LindemannRock/craft-slideshow-manager/commit/318efc3823a5f093274d262c9944bf34aed15097))

## [5.4.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.3.0...v5.4.0) (2025-12-04)


### Features

* add PHPStan and EasyCodingStandard configurations, enhance plugin settings, and clean up code ([498ca9b](https://github.com/LindemannRock/craft-slideshow-manager/commit/498ca9b62ac54dc4b811bbb29dcfca52117db7ad))
* add properties for slide details including title, description, category, and content ([f9ea334](https://github.com/LindemannRock/craft-slideshow-manager/commit/f9ea33408df7fbaafed54ed16d752a5af86c8cfe))


### Bug Fixes

* simplify settings loading logic in SettingsController ([1b5682a](https://github.com/LindemannRock/craft-slideshow-manager/commit/1b5682a462ffe5ef2cc17bfe3f047e2e6bfdfba3))
* update method signatures to allow nullable context parameters in defineSources and defineActions ([c699807](https://github.com/LindemannRock/craft-slideshow-manager/commit/c699807e399e0462149036b2aae2430011e7d0f8))


### Miscellaneous Chores

* add [@since](https://github.com/since) 1.0.0 annotation to multiple files for versioning clarity ([13da821](https://github.com/LindemannRock/craft-slideshow-manager/commit/13da8215ec87bbc1a56b67950b9d879b4b949527))

## [5.3.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.2.0...v5.3.0) (2025-11-15)


### Features

* add MIT License file to the repository ([e63c717](https://github.com/LindemannRock/craft-slideshow-manager/commit/e63c7178afacec72dee969a97ea9b90cb8ae65e3))
* enhance plugin settings with display name helpers and update Twig templates for breadcrumbs ([d202b1d](https://github.com/LindemannRock/craft-slideshow-manager/commit/d202b1d9583f895f78f977bc3ad7ef83bf985de2))

## [5.2.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.1.0...v5.2.0) (2025-10-27)


### Features

* refactor settings handling in SettingsController and update template warnings ([5685fa3](https://github.com/LindemannRock/craft-slideshow-manager/commit/5685fa36a7a4dcb23a62a43c30d5d5b39f2a9439))


### Bug Fixes

* update README and logging documentation with new log level adjustments and additional configuration details ([40cbd7d](https://github.com/LindemannRock/craft-slideshow-manager/commit/40cbd7d5696214fe7eeb0098cdba359d428ffa8d))

## [5.1.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v5.0.1...v5.1.0) (2025-10-22)


### Features

* add itemsPerPage setting to SlideshowManager configuration ([b714db2](https://github.com/LindemannRock/craft-slideshow-manager/commit/b714db26d4cf964a36ac3794dbf7057770c967dd))

## [5.0.1](https://github.com/LindemannRock/craft-slideshow-manager/compare/v1.2.3...v5.0.1) (2025-10-20)


### Miscellaneous Chores

* update logging library dependency to version 5.0 and enhance README with additional badges ([e6362b1](https://github.com/LindemannRock/craft-slideshow-manager/commit/e6362b1a282ad3733a0ae2770b8c051e79dbc517))

## [1.2.3](https://github.com/LindemannRock/craft-slideshow-manager/compare/v1.2.2...v1.2.3) (2025-10-17)


### Bug Fixes

* use settings for plugin name in logging configuration ([a2894ab](https://github.com/LindemannRock/craft-slideshow-manager/commit/a2894ab1726f0b7c2b91d386ac7c02c04a9002d2))

## [1.2.2](https://github.com/LindemannRock/craft-slideshow-manager/compare/v1.2.1...v1.2.2) (2025-10-16)


### Bug Fixes

* update installation instructions for Composer and DDEV ([dc02104](https://github.com/LindemannRock/craft-slideshow-manager/commit/dc021048fd69bbbc4f398f2fe77d62ccd8091cf4))

## [1.2.1](https://github.com/LindemannRock/craft-slideshow-manager/compare/v1.2.0...v1.2.1) (2025-10-16)


### Bug Fixes

* remove logging-library repository configuration from composer.json ([b4e2339](https://github.com/LindemannRock/craft-slideshow-manager/commit/b4e2339045a8fc2b0ed6e4ffb89e7865efae908f))

## [1.2.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v1.1.0...v1.2.0) (2025-10-16)


### Features

* update README and add detailed logging documentation ([93d461c](https://github.com/LindemannRock/craft-slideshow-manager/commit/93d461cef0b0a9cfa84b458ddf83de22d56d079c))

## [1.1.0](https://github.com/LindemannRock/craft-slideshow-manager/compare/v1.0.0...v1.1.0) (2025-10-16)


### Features

* add comprehensive logging with structured context arrays ([f9ed377](https://github.com/LindemannRock/craft-slideshow-manager/commit/f9ed37786feb02a3fc0ae5bd466838fbe3326885))

## 1.0.0 (2025-10-15)


### Features

* initial Slideshow Manager plugin implementation ([22e3aa1](https://github.com/LindemannRock/craft-slideshow-manager/commit/22e3aa13e5728c101cf306c68c8e42664f66cc8a))
