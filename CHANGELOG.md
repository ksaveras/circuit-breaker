# Changelog

## [2.1.0](https://www.github.com/ksaveras/circuit-breaker/compare/v2.0.2...v2.1.0) (2024-05-11)


### Features

* Add support for SF 7.0, add PHP 8.3 ([#20](https://www.github.com/ksaveras/circuit-breaker/issues/20)) ([c1e3d7e](https://www.github.com/ksaveras/circuit-breaker/commit/c1e3d7e093dca649c3a0020ed6e28669c5b40d0a))


### Bug Fixes

* **deps:** update symfony packages to v7.0.7 ([#28](https://www.github.com/ksaveras/circuit-breaker/issues/28)) ([ac2f2eb](https://www.github.com/ksaveras/circuit-breaker/commit/ac2f2eb207c36211fc7cdbd7ece51f238e9e21ff))


### Miscellaneous Chores

* bump dev dependencies ([#24](https://www.github.com/ksaveras/circuit-breaker/issues/24)) ([68e3413](https://www.github.com/ksaveras/circuit-breaker/commit/68e3413e595b161cb80e3ec458f0bac760f6632a))
* bump GH action versions ([#23](https://www.github.com/ksaveras/circuit-breaker/issues/23)) ([34f49c0](https://www.github.com/ksaveras/circuit-breaker/commit/34f49c0cfd64dd5c59da7848fbbe427d6f7d3c1f))
* Configure renovate ([#26](https://www.github.com/ksaveras/circuit-breaker/issues/26)) ([d2be349](https://www.github.com/ksaveras/circuit-breaker/commit/d2be3494eb8aa7e2bd65d0e0c167e7593f7abead))
* Configure renovate ([#34](https://www.github.com/ksaveras/circuit-breaker/issues/34)) ([0673085](https://www.github.com/ksaveras/circuit-breaker/commit/0673085f56527608b2d22ef10aae613be54b1b93))
* **deps:** update actions/upload-artifact action to v4 ([#29](https://www.github.com/ksaveras/circuit-breaker/issues/29)) ([21eb958](https://www.github.com/ksaveras/circuit-breaker/commit/21eb958de532239bf69952fea2be921396d6705d))
* **deps:** update dawidd6/action-download-artifact action to v3 ([#30](https://www.github.com/ksaveras/circuit-breaker/issues/30)) ([b57b77f](https://www.github.com/ksaveras/circuit-breaker/commit/b57b77f58e109ec31dddb39201ef06ca932d4316))
* **deps:** update dependency friendsofphp/php-cs-fixer to v3.56.1 ([#33](https://www.github.com/ksaveras/circuit-breaker/issues/33)) ([c9f8d21](https://www.github.com/ksaveras/circuit-breaker/commit/c9f8d21b1f34c32a7882368e347d071d346b4404))
* **deps:** update dependency rector/rector to v1.0.5 ([#27](https://www.github.com/ksaveras/circuit-breaker/issues/27)) ([d41ce8c](https://www.github.com/ksaveras/circuit-breaker/commit/d41ce8c0150cd46ff742966d41ec7532a565b8ab))
* Exclude branches from coverage report ([#25](https://www.github.com/ksaveras/circuit-breaker/issues/25)) ([d7bf879](https://www.github.com/ksaveras/circuit-breaker/commit/d7bf879b8402b6de9b7433355b7be79e6044dea9))

### [2.0.2](https://www.github.com/ksaveras/circuit-breaker/compare/v2.0.1...v2.0.2) (2023-06-24)


### Bug Fixes

* Use Symfony Clock 6.3 lowest version ([#18](https://www.github.com/ksaveras/circuit-breaker/issues/18)) ([f46ddd4](https://www.github.com/ksaveras/circuit-breaker/commit/f46ddd4dc72306f497fa822f87e44c045eb3b5e5))

### [2.0.1](https://www.github.com/ksaveras/circuit-breaker/compare/v2.0.0...v2.0.1) (2023-06-24)


### Bug Fixes

* Add psr/http-message 1.0 support ([#16](https://www.github.com/ksaveras/circuit-breaker/issues/16)) ([23eaf3f](https://www.github.com/ksaveras/circuit-breaker/commit/23eaf3fd9e44843b8699a0e4839910bbb501d8f5))

## [2.0.0](https://www.github.com/ksaveras/circuit-breaker/compare/v1.0.0...v2.0.0) (2023-06-24)


### ⚠ BREAKING CHANGES

* Refactor Circuit Breaker, bump supported PHP versions (#9)

### Features

* Refactor circuit breaker ([#14](https://www.github.com/ksaveras/circuit-breaker/issues/14)) ([6c114f2](https://www.github.com/ksaveras/circuit-breaker/commit/6c114f23f3c85867ee2dc4fd0f0aed4b0319e44d))
* Refactor Circuit Breaker, bump supported PHP versions ([#9](https://www.github.com/ksaveras/circuit-breaker/issues/9)) ([1ae4d1d](https://www.github.com/ksaveras/circuit-breaker/commit/1ae4d1d2dba5ea02b7531df285fa3d87d71bff72))
* Use Retry-After and Rate-Limit-Reset HTTP headers ([#15](https://www.github.com/ksaveras/circuit-breaker/issues/15)) ([6f04be2](https://www.github.com/ksaveras/circuit-breaker/commit/6f04be2ce65ace39288b0d159db2e15c0511fb19))


### Bug Fixes

* Float type exponential base ([#12](https://www.github.com/ksaveras/circuit-breaker/issues/12)) ([bb36e76](https://www.github.com/ksaveras/circuit-breaker/commit/bb36e7650a05ee0e7a861a82e0d816606d313a4e))


### Miscellaneous Chores

* Remove default values, rename properties ([#13](https://www.github.com/ksaveras/circuit-breaker/issues/13)) ([89c6736](https://www.github.com/ksaveras/circuit-breaker/commit/89c6736fe0a89552ae62625a3482f0157056e1b3))
* Use custom GH token ([#11](https://www.github.com/ksaveras/circuit-breaker/issues/11)) ([f300884](https://www.github.com/ksaveras/circuit-breaker/commit/f30088429e80025656447663ecb69586bf30b492))
