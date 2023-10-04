<?php

class SkynamoClearworld
{
    //toggle development or production environment.
    const IS_DEV = 2;

    //DEVELOPMENT environment
    const DEV_API_KEY  = '7YoJeIkJhq3gtH14WONmjaFrsbR4Y0im2llgwwjy';
    const DEV_CLIENTNAME = 'trienergyqa';

    //PRODUCTION environment
    const PROD_API_KEY    = 'faDccW7lh186gmylt4yGX17ujjm1v0qz4RPYN2U4';
    const PROD_CLIENTNAME = 'trienergy';

    static function getAPIKey()
    {
        if (IS_DEV==1) {
            return self::DEV_API_KEY;
        }
        return self::PROD_API_KEY;
    }

    static function getClientName()
    {
        if (IS_DEV==1) {
            return self::DEV_CLIENTNAME;
        }
        return self::PROD_CLIENTNAME;
    }

}