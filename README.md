<p align="center">
<img src="https://github.com/DeGraciaMathieu/php-smelly-code-detector/blob/master/arts/robot.png" width="250">
</p>

# php-class-dependencies-analyzer

[![testing](https://github.com/DeGraciaMathieu/php-coupling-instability-analyzer/actions/workflows/testing.yml/badge.svg)](https://github.com/DeGraciaMathieu/php-coupling-instability-analyzer/actions/workflows/testing.yml)

> 🇫🇷 For French developers, an [article](https://laravel-france.com/posts/des-dependances-stables-pour-une-architecture-de-qualite) on Laravel France explores these stable dependency concepts.

## Phar
This tool is distributed as a [PHP Archive (PHAR)](https://www.php.net/phar):

```
wget https://github.com/DeGraciaMathieu/php-class-dependencies-analyzer/raw/main/builds/class-dependencies-analyzer
```

```
php class-dependencies-analyzer --version
```

## Why

Inevitably, in an object-oriented project, classes will depend on each other.

These dependencies generate instability that can be measured with the following formula:

```
Instability = Efferent / (Afferent + Efferent)
```

Afferent coupling represents the number of components that depend on a given component, while efferent coupling counts the number of dependencies a given component has.

<img src='https://cdn.laravel-france.com/images/pictures/6aa50c00-414c-4817-928d-c67d1bf996e0.png'>

Instability ranges between 0 and 1, where 0 represents a fully stable class and 1 represents a fully unstable class.

Stable class has few dependencies but is depended on by many components. Therefore, a stable class is critical to the project and must be reliable and well-tested.

An unstable class has many dependencies but few components depend on it. Therefore, it’s easier to modify, but it’s more likely to be affected by changes in its dependencies.
It is recommended to monitor the dependencies of an unstable component and replace those that are even more unstable with an abstraction.

It’s important to manage these dependencies between classes to maintain maintainable, modular, and easily testable code.

This tool allows you to:

- Detect classes with a high coupling instability rate
- Detect cyclic dependencies among a set of classes
- Detect fragile dependencies between classes

## Visualizing Instability

To visualize the instability and coupling of your classes:

```
php class-dependencies-analyzer analyze ./path/to/project
```

⚠️ To obtain accurate results, it is important to analyze your entire codebase, use the `--only=*` or `--exclude=*` options to filter the results.

```
php class-dependencies-analyzer analyze app --only=App\\Domain,Infrastructure
php class-dependencies-analyzer analyze app --exclude=Models
```

## Detecting Cyclic Dependencies

A dependency cycle is a class that depends on itself through its dependencies.

<img src='https://cdn.laravel-france.com/images/pictures/34ddeac2-d9ac-4ebb-a54c-48f7940945ca.png'>

This violates the [acyclic dependencies principle](https://en.wikipedia.org/wiki/Acyclic_dependencies_principle), it can be a sign of a bad design and reveal an ensemble of components difficult to maintain and evolve.

```
php class-dependencies-analyzer cyclic ./path/to/project
```

> You can filter the results using the `--only=*` or `--exclude=*` options.

## Detecting Fragile Dependencies

A fragile dependancy is a class that depends on a class that is more unstable than it.

It can be a sign of a bad design and an indicator of a class that can suffer from side effects of its dependencies.

The first class will be sensitive to any changes made to the second class and will experience side effects.

<img src='https://cdn.laravel-france.com/images/pictures/c80b59f3-ffad-4609-9364-f8efa4e62c9a.png'>

These dependencies can generate bugs and difficulties in evolution.

```
php class-dependencies-analyzer weakness ./path/to/project
```

To filter results based on an instability delta, you can use the `--min-delta` option.

```
php class-dependencies-analyzer weakness ./path/to/project --min-delta=0.1
```

The delta corresponds to the difference in stability between a class and one of its dependencies. The greater this difference, the more likely the first component is to suffer from side effects caused by its unstable dependency.

> You can filter the results using the `--only=*` or `--exclude=*` options.
