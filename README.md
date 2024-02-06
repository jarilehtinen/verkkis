# Verkkis

Small command line program to get products from [Verkkokauppa.com Outlet](https://www.verkkokauppa.com/fi/outlet/yksittaiskappaleet).

### Add alias to .bashrc, .zshrc etc.

```shell
alias verkkis="php [PATH]/index.php"
```

### Update product listings

```shell
verkkis update
```

### List recently added products

List 10 most recent products:

```shell
verkkis new
```

Define own product limit:

```shell
verkkis new 100
```

### Searching for a product

Using fuzzy search:

```shell
verkkis Sonos One
```

which matches all products containing "Sonos" and "One".

Or if you want to search with the exact phrase:

```shell
verkkis "Sonos One"
```

### Saving searches

```shell
verkkis save "Sonos One"
```

After saving you can just type:

```shell 
verkkis
```

which shows results for all your searches.

### Removing saved search

List saved searches:

```shell
verkkis list
```

To remove a specific search:

```shell
verkkis remove [id]
```

### See help

```shell
verkkis --help
```