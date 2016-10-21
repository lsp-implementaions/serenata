## 1.3.0
### Features and improvements
* A great deal of refactoring has occurred, which paved the way for performance improvements in several areas, such as type deduction.
  * Indexing should be slightly faster.
  * Everything should feel a bit more responsive.
  * Semantic linting should be significantly faster, especially for large files.
* The type of built-in global constants is now deduced from their default value as Reflection can't be used to fetch their type nor do we have any documentation data about them..
* A new command, `--namespace-list`, is now available, which can optionally be filtered by file, to retrieve a list of namespaces. (thanks to [pszczekutowicz](https://github.com/pszczekutowicz))
* When a class has a method that overrides a base class method and implements an interface method from one of its own interfaces, both the `implementation` and `override` data will now be set as they are both relevant.
* `ResolveType` and `LocalizeType` now support a `kind` parameter to determine the kind of the type (or rather: name) that needs to be resolved.
  * This is necessary to distinguish between classlike, constant and function name resolving based on use statements. (Yes, duplicate use statements may exist in PHP, as long as their `kind` is different).

### Bugs fixed
* Unqualified global constants will now correctly be resolved.
* Unqualified global functions will now correctly be resolved.
* Documentation for built-in functions was escaping underscores with a slash.
* Built-in interface methods had `isAbstract` set to `true` instead of `false`.
* Semantic linting was incorrectly processing unqualified global function names.
* Semantic linting was incorrectly processing unqualified global constant names.
* The status bar was not showing progress when a project index happened through a repository status change.
* In single line docblocks, the terminator `*/` was not being ignored (and taken up in the last tag's data).
* Use statements for constants (i.e. `use const`) will now be properly analyzed when checking for unused use statements.
* Use statements for functions (i.e. `use function`) will now be properly analyzed when checking for unused use statements.
* Class annotations were sometimes being picked up as being part of the description of other tags (such as `@var`, `@param`, ...).
* Use statements were incorrectly reported as unused when they were being used for extension or implementation by anonymous classes.
* Editing a file that did not meet the allowed extensions specified in the project settings still caused it to be added to the index.
* Assigning a global constant to something caused the type of that something to become the name of the constant as class name instead.
* Parantheses inside strings were sometimes interfering with invocation info information, causing the wrong information to be returned.
* Variadic parameters with type hints were incorrectly matched with their docblock types and, by consequence, incorrectly reported as having a mismatching type.
* Previously a fix was applied to make FQCN's actually contain a leading slash to clearly indicate that they were fully qualified. This still didn't happen everywhere, which has been corrected now.
* Caching will now add an additional folder with the name of the active user in it. This solves a problem where instances from multiple users on the same system would try to use the same cache entries.
* Parent members of built-in classlikes were being indexed twice: once for the parent and once for the child, which was resulting in incorrect inheritance resolution results, unnecessary data storage and a (minor) performance hit.
* Built-in interfaces no longer have `isAbstract` set to true. They _are_ abstract in a certain sense, but this property is meant to indicate if a classlike has been defined using the abstract keyword. It was also not consistent with the behavior for non-built-in interfaces.
* The indexer was assigning an incorrect type to variadic parameters. You can now use elements of type hinted variadic parameters as expected in a foreach:

```php
protected function foo(Bar ...$bars)
{
    foreach ($bars as $bar) {
        // $bar is now an instance of Bar.
    }
}
```

## 1.2.0
* Initial split from the [php-integrator/atom-base](https://github.com/php-integrator/atom-base) repository. See [its changelog](https://github.com/php-integrator/atom-base/blob/master/CHANGELOG.md) for what changed in older versions.
