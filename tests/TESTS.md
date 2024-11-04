# Test Specification

This document outlines the test specification for the Spark Module.

---

## Architecture Tests

### [Architecture](pest/Architecture/ArchitectureTest.php)

_Tests the architecture of the plugin._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Source code does not contain any `Craft::dd` statements.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Source code does not contain any `var_dump` or `die` statements.  

## Feature Tests

### [Functions](pest/Feature/FunctionsTest.php)

_Tests the Spark functions._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test that the `spark()` function throws an exception when an object variable exists.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test the `sparkStore()` function.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test that the `sparkStore()` function throws an exception when an object item exists.  
