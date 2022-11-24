<?php

// src/Command/CreateUserCommand.php
namespace App\Command;

use App\Repository\DelayedOrdersRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This methods gets the delayed records from orders table and inserts them into delayed orders.
 * Both the actions are handled in single query.
 * This could have been made better using Entity or Repository but as I am new to Symfony, I did it this way.
 */
// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:delayed-orders')]
class DelayedOrdersCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this
            ->setHelp('This command allows you to create entries in delayed orders table...');
        
        $output->writeln([
            'Creating entries for delayed orders in table',
            '============',
            '',
        ]);
    
        $mysqli = new \mysqli("localhost","root","","electric_miles");

        // Check connection
        if ($mysqli -> connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
            exit();
        }

        $nowDate = date('Y:m:d H:i:s', time());

        // Single query to fetch from orders table and insert into delayed orders
        $sql = "INSERT INTO `delayed_orders` (`order_id`, `expected_delivery_time`, 
        `created_at`, `updated_at`) 
        SELECT `o`.`id`, `o`.`expected_delivery_time`, '$nowDate', '$nowDate' 
        FROM `orders` o LEFT JOIN `delayed_orders` dor ON `o`.`id` = `dor`.`order_id` 
        WHERE `o`.`expected_delivery_time` <= '$nowDate' AND `dor`.`order_id` IS NULL;";

        if ($mysqli -> query($sql)) {
            $count = $mysqli->affected_rows;
            print("Inserted $count delayed orders\n");
            return Command::SUCCESS;
        } else {
            print("Error in inserting records\n");
            return Command::FAILURE;
        }
    }
}