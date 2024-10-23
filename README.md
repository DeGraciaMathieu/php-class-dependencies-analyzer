<p align="center">
<img src="https://github.com/DeGraciaMathieu/php-smelly-code-detector/blob/master/arts/robot.png" width="250">
</p>

# php-git-insights-analyzer

## Phar
This tool is distributed as a [PHP Archive (PHAR)](https://www.php.net/phar):

```
wget https://github.com/DeGraciaMathieu/php-coupling-instability-analyzer/raw/master/builds/php-coupling-instability-analyzer
```

```
php coupling-instability-analyzer --version
```

## Why

> 🇫🇷 For French developers, an article on Laravel France explores this concept of coupling and instability: [Couplage et stabilité des classes](https://laravel-france.com/posts/couplage-et-stabilite-des-classes)

Inevitably, in an object-oriented project, classes will depend on each other.

These dependencies generate instability that can be measured with the following formula:

```
Instability = Efferent / (Afferent + Efferent)
```

Afferent coupling represents the number of components that depend on a given component, while efferent coupling counts the number of dependencies a given component has.

<img src='https://cdn.laravel-france.com/images/pictures/dc05744c-27ad-45cd-b1a2-97af33e8c4ae.png'>

Instability ranges between 0 and 1, where 0 represents a fully stable class and 1 represents a fully unstable class.

A stable class has few dependencies but is depended on by many components. Therefore, a stable class is critical to the project and must be reliable and well-tested.

An unstable class has many dependencies but few components depend on it. Therefore, it’s easier to modify, but it’s more likely to be affected by changes in its dependencies.

It’s important to manage these dependencies between classes to maintain maintainable, modular, and easily testable code.

This tool allows you to:

- Detect classes with a high coupling instability rate
- Detect cyclic dependencies among a set of classes
- Detect fragile dependencies between classes

## Visualizing Instability

To visualize the instability and coupling of your classes:

```
php coupling-instability-analyzer analyze ./path/to/project
```

⚠️ To obtain accurate results, it is important to analyze your entire codebase.

Use the `--filter=*` option to filter the results.

```
php coupling-instability-analyzer analyze app --filters=Domain,Infrastructure
```

## Detecting Cyclic Dependencies

A dependency cycle is a set of classes that depend on each other in a cyclical manner.

<img src='https://cdn.laravel-france.com/images/pictures/34ddeac2-d9ac-4ebb-a54c-48f7940945ca.png'>

This violates the [acyclic dependencies principle](https://en.wikipedia.org/wiki/Acyclic_dependencies_principle), cyclic dependencies often lead to bugs and maintenance difficulties.

```
php coupling-instability-analyzer cyclic ./path/to/project
```

> You can filter the results using the `--filters` option.

## Detecting Fragile Dependencies

A fragile dependency is a component that depends on another component that is more unstable than itself.

The first component will be sensitive to any changes made to the second component and will experience side effects.

These dependencies can generate bugs and difficulties in evolution.

```
php coupling-instability-analyzer weakness ./path/to/project
```

To filter results based on an instability threshold, you can use the `--min-score` option.
```
php coupling-instability-analyzer weakness ./path/to/project --min-score=0.1
```

The threshold corresponds to the difference in stability between a class and one of its dependencies. The greater this difference, the more likely the first component is to suffer from side effects caused by its unstable dependency.

> You can filter the results using the `--filters` option.
