# sfDoctrineOAuthPlugin #

If you want to use OAuth with many services out of box, you can use [sfMelodyPlugin](http://www.symfony-project.org/plugins/sfMelodyPlugin "sfMelodyPlugin").

It's an implementation of OAuth version 1 and 2 for easy using with symfony.

Feel free to contribute on github : [sfDoctrineOAuthPlugin](http://github.com/chok/sfDoctrineOAuthPlugin "sfDoctrineOAuthPlugin")

## Installation ##

 * Install
 
      $ symfony plugin:install sfDoctrineOAuthPlugin
   
 * Clear cache
 
      $ symfony cc
          
 * Rebuild model and db by generating migrations (thanks to [gimler](http://github.com/gimler) for this parts)

        $ symfony doctrine:generate-migrations-diff

        $ symfony doctrine:build --all-classes

        $ symfony doctrine:migrate
        
  * Or all in one
  
        $ symfony doctrine:build --all
 

## TODO ##
 * Documentation ;-)
