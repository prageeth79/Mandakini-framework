
Composer

    Command Line:
        composer init

        initialze the composer file by asking deatils of the project
        
    COmmad Line:
        Composer update

        this command update composer file to apply changes 

    command Line:
        composer require vlucas/phpdotenv
        setup .env files 
        site: https://github.com/vlucas/phpdotenv


        read content of the file:
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->load();

        use content of the file:
            the content is accessed by $_ENV
            $config = [
                'db' => [
                    'dsn' => $_ENV['DB_DSN'] ?? 'mysql:host=localhost;port=3306;dbname=mandakini',
                    'username' => $_ENV['DB_USER'] ?? 'root',
                    'password' => $_ENV['DB_PASSWORD'] ?? ''
                ]
            ];
        