# Verkkis

Small command line program to get products from [Verkkokauppa.com Outlet](https://www.verkkokauppa.com/fi/outlet/yksittaiskappaleet).

#### Add alias to .bashrc, .zshrc etc.

```shell
alias verkkis="php [PATH]/index.php"
```

#### Update product listings

```shell
verkkis update
```

#### Searching for a product

Using fuzzy search:

```shell
verkkis Sonos One
```

which matches all products containing "Sonos" and "One".

Or if you want to search with the exact phrase:

```shell
verkkis "Sonos One"
```

#### See other options

```shell
verkkis --help
```