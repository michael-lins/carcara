<?php
/**
 * Carcara (http://carcara.candango.org)
 *
 * @link      http://github.com/candango/carcara
 * @copyright Copyright (c) 2018 Flavio Garcia
 * @license   https://www.apache.org/licenses/LICENSE-2.0  Apache-2.0
 */

namespace Candango\Carcara\Commands
{

    use Candango\Carcara\AbstractCommand;
    use Candango\Carcara\Engine\AbstractDaoGenerator;
    use Candango\Carcara\Engine\AbstractDatabaseLoader;
    use Candango\Carcara\Engine\DatabaseLoader;
    use Candango\Carcara\Factory;
    use Candango\Carcara\File;
    use Candango\Carcara\Lexicon;
    use Candango\Carcara\Model\Conf;
    use Candango\Carcara\SmartyInABox;
    use GetOpt\GetOpt;
    use GetOpt\Operand;

    class DaoCommand extends AbstractCommand
    {
        public function brief()
        {
            return "Execute actions related to DAO.";
        }

        function getOperands()
        {
            $actionOperandTpl = "commands/dao/action_operand.tpl";
            return [
                Operand::create("action", Operand::REQUIRED)->setDescription(
                    SmartyInABox::fetch($actionOperandTpl)),
                Operand::create("conf", Operand::OPTIONAL)->setDescription(
                    "Carcara conf name")
            ];
        }

        /**
         * @param $getopt GetOpt
         */
        public function run($getopt)
        {
            $conf = new Conf();

            $action = $getopt->getOperand('action');

            $name = $getopt->getOperand('conf') ?
                $getopt->getOperand('conf') : "default";

            $conf->setName($name);

            $allowedActions = ["gen", "generate"];

            if (!in_array($action, $allowedActions)) {
                echo sprintf("The action %s is invalid.\n\n", $action);
                echo $getopt->getHelpText();
                exit(1);
            }

            switch ($action){
                case "gen":
                case "generate":
                    $this->generateDaoAction($conf);
                    break;
            }
            exit(0);
        }

        private function generateDaoAction(Conf $conf)
        {
            echo sprintf("Generating DAO for Data Source %s\n",
                $conf->getName());
            echo "Checking if the conf file exists ... ";
            if (file_exists($conf->getFilePath())) {
                echo "[ OK ]\n";
                $conf = Factory::getConf($conf->getName());
                echo sprintf("Connecting to the database %s ... ",
                    $conf->getDatabase());
                $loader = AbstractDatabaseLoader::getLoader($conf);
                try {
                    $loader->connect();
                } catch (\PDOException $e) {
                    echo "[ FAIL ]\n";
                    echo "ERROR: " . $e->getMessage() .
                        ".\nCheck your configuration.\n";
                    exit(3);
                }
                echo "[ OK ]\n";
                echo "Loading tables ... ";
                $loader->doLoad();
                echo "[ OK ]\n";
                echo sprintf("%s tables loaded.\n",
                    count($loader->getTables()));
                $loader->disconnect();
                echo sprintf("Disconnected from the database %s.\n",
                    $conf->getDatabase());

                $this->prepareDaoPaths($conf, $loader);

                $generator = AbstractDaoGenerator::getGenerator($loader);

                SmartyInABox::getInstance()->assign("conf", $conf);
                SmartyInABox::getInstance()->assign("identifierName",
                    Lexicon::getEntityName($conf->getIdentifier()));
                echo "\nGenerating DAO factories ... ";
                $daoFactories = $generator->generateDaoFactories();
                echo "[ OK ]\n";

                $this->storeDaoFactories($conf, $daoFactories);

                echo "\nGenerating DTOs ... ";
                $dtos = $generator->generateDtos();
                echo "[ OK ]\n";
                $this->storeDtos($conf, $dtos);

                echo "\nGenerating DAOs ... ";
                $daos = $generator->generateDaos();
                echo "[ OK ]\n";
                $this->storeDaos($conf, $daos);
            } else {
                echo "[ FAIL ]\n";
                echo sprintf("File %s doesn't exists.\n",
                    $conf->getFilePath());
                exit(1);
            }
        }

        /**
         * @param Conf $conf
         * @param DatabaseLoader $loader
         */
        private function prepareDaoPaths(Conf $conf, DatabaseLoader $loader)
        {
            $libDir = $conf->getLibDir();
            echo sprintf("Checking if lib dir exists at %s ... ", $libDir);
            if (!file_exists($libDir)) {
                echo "[ NOT FOUND ]\n";
                echo "Creating lib directory ... ";
                if(mkdir($libDir)) {
                    echo "[ OK ].\n";
                } else {
                    echo "[ ERROR ].\n";
                    exit(1);
                }
            } else {
                echo "[ OK ]\n";
            }

            $daoDir = $conf->getDaoDir();
            $currentDaoDir = $conf->getCurrentDaoDir();

            echo sprintf("Checking if DAO dir exists at %s ... ", $daoDir);
            if (!file_exists($daoDir)) {
                echo "[ NOT FOUND ]\n";
                echo "Creating DAO directory ... ";
                if(mkdir($daoDir)) {
                    echo "[ OK ].\n";
                } else {
                    echo "[ ERROR ].\n";
                    exit(1);
                }
            } else {
                echo "[ OK ]\n";
            }

            echo sprintf("Checking if Current DAO dir exists at %s ... ",
                $currentDaoDir);
            if (!file_exists($currentDaoDir)) {
                echo "[ NOT FOUND ]\n";
                echo "Creating Current DAO directory ... ";
                if(mkdir($currentDaoDir)) {
                    echo "[ OK ].\n";
                } else {
                    echo "[ ERROR ].\n";
                    exit(1);
                }
            } else {
                echo "[ OK ]\n";
            }

            echo "\nCreating entity dirs:\n";

            foreach ($loader->getTables() as $table) {
                $entity = Lexicon::getTableEntityName($table);
                $entityDir = sprintf("%s%s%s", $currentDaoDir,
                    DIRECTORY_SEPARATOR, $entity);
                echo sprintf("Checking if %s entity dir exists at %s ... ",
                    $entity,  $entityDir);
                if (!file_exists($entityDir)) {
                    echo "[ NOT FOUND ]\n";
                    echo sprintf("Creating %s entity dir ... ", $entity);
                    if(mkdir($entityDir)) {
                        echo "[ OK ].\n";
                    } else {
                        echo "[ ERROR ].\n";
                        exit(1);
                    }
                } else {
                    echo "[ OK ]\n";
                }
            }

        }

        /**
         * @param $conf Conf
         * @param $daoFactories
         */
        private function storeDaoFactories($conf, $daoFactories)
        {
            echo "Storing DAO Factories:\n";
            $currentDaoDir = sprintf("%s%s", $conf->getCurrentDaoDir(),
                DIRECTORY_SEPARATOR);
            foreach ($daoFactories as $key => $daoFactory) {
                try {
                    echo sprintf("Storing %s DAO Factory ... ", $key);
                    File::write($currentDaoDir . $daoFactory['path'],
                        $daoFactory['code']);
                    echo "[ OK ]\n";
                } catch (\Exception $e) {
                    echo $e->getMessage() . "\n";
                    exit(4);
                }


            }
        }

        private function storeDtos($conf, $dtos)
        {
            echo "Storing DTOs:\n";
            $currentDaoDir = sprintf("%s%s", $conf->getCurrentDaoDir(),
                DIRECTORY_SEPARATOR);
            foreach ($dtos as $key => $dtoTypes) {
                echo sprintf("Storing %s DTOs:\n", $key);
                foreach ($dtoTypes as $type => $dto) {
                    try {
                        echo sprintf("    - %s ... ", $type);
                        if (!$dto['always']) {
                            if (file_exists($currentDaoDir . $dto['path'])) {
                                echo "[ ALREADY EXISTS SKIPPING ]\n";
                                continue;
                            }
                        }
                        File::write($currentDaoDir . $dto['path'],
                            $dto['code']);
                        echo "[ OK ]\n";
                    } catch (\Exception $e) {
                        echo $e->getMessage() . "\n";
                        exit(4);
                    }
                }
            }
        }

        private function storeDaos($conf, $daos)
        {
            echo "Storing DAOs:\n";
            $currentDaoDir = sprintf("%s%s", $conf->getCurrentDaoDir(),
                DIRECTORY_SEPARATOR);
            foreach ($daos as $key => $daoTypes) {
                echo sprintf("Storing %s DAOs:\n", $key);
                foreach ($daoTypes as $type => $dao) {
                    try {
                        echo sprintf("    - %s ... ", $type);
                        if (!$dao['always']) {
                            if (file_exists($currentDaoDir . $dao['path'])) {
                                echo "[ ALREADY EXISTS SKIPPING ]\n";
                                continue;
                            }
                        }
                        File::write($currentDaoDir . $dao['path'],
                            $dao['code']);
                        echo "[ OK ]\n";
                    } catch (\Exception $e) {
                        echo $e->getMessage() . "\n";
                        exit(4);
                    }
                }
            }
        }
    }
}
