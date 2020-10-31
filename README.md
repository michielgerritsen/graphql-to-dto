# Generate DTO's from a GraphQL endpoint

This repository allows you to feed an GrahpQL endpoint so it can create DTO's for you.

## What is a DTO?

DTO stands for **D**ata **T**ransfer **O**bject. It means you can put data in and you can get data out. But the data inside is immutable, which means that you can't alter it once it is in.

## What is the use case for this?

When working with GraphQL you might receive a nested array of data, which can be hard to work with, you don't know if the data is complete and you need to know exactly what is in the array before you can work with it. A DTO makes sure that all data is there: If it's not PHP will fail initiating the object and throw an error. Also, because of typehinting PHP makes sure that all data that goes in is in the correct form. Last but not least, if you have a good IDE it will autocomplete your code so you don't have to look up what you exactly receive from the endpoint, you IDE already knows.

# Installation

```
git clone git@github.com:michielgerritsen/graphql-to-dto.git
cd graphql-to-dto
composer install
```

# Usage

```
./bin/graphql-to-dto generate <url> <namespace>
./bin/graphql-to-dto generate https://the-url-to-your-grahpql-endpoint.tld/graphql YourVendorName/Module/DTO
```

The DTO's will get outputted to the `output` folder. By default deprecated attributes are ignored. To include these you can use the `--include-deprecated` or `-d` flag:

```
./bin/graphql-to-dto generate <url> <namespace> --include-deprecated 
```

# Warning

This method is not perfect and may have some rough edges, always check the generated code before using it. If you find some, create an [issue](https://github.com/michielgerritsen/graphql-to-dto/issues/new) or fix it yourself and create a pull request.

# Wishlist

- Add a static `fromArray` method to initiate the DTO's faster.
- Support for Enums.
- Ignore/allowlist wich calculates the required dependencies.
- Correct fragments handling
- Tests, tests, tests.
