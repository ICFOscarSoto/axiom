<?php
namespace App\Modules\Globale\Services;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Doctrine\ORM\EntityManager;
use App\Modules\Globale\Entity\GlobaleUsers;
use Symfony\Component\Security\Core\SecurityContext;
use \PDO;

class SessionHandler extends PdoSessionHandler
{
    /**
     * @var \PDO PDO instance.
     */
    private $pdo;

    /**
     * @var array Database options.
     */
    private $dbOptions;


    protected $tokenStorage;
    protected $entityManager;
    protected $uriExceptions;  //Routes no update lastActivity

    public function __construct(array $dbOptions, TokenStorage $tokenStorage,EntityManager $entityManager)
    {
        $this->pdo = new \PDO("mysql:host=".$dbOptions["db_host"].";dbname=".$dbOptions["db_base"].";",$dbOptions["db_username"],$dbOptions["db_password"]);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        /*$this->dbOptions = array_merge(
            array('db_user_id_col' => 'user_id'),
            $dbOptions
        );*/
        $this->dbOptions = $dbOptions;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->uriExceptions = ["/es/admin/api/notifications/unreadlist", "/api/emails/unreadlist"];

        parent::__construct($this->pdo, $this->dbOptions);
    }

    public function read($id)
    {
        // get table/columns
        $dbTable   = $this->dbOptions['db_table'];
        $dbDataCol = $this->dbOptions['db_data_col'];
        $dbIdCol   = $this->dbOptions['db_id_col'];

        try {
            $sql = "SELECT $dbDataCol, kick, $dbIdCol FROM $dbTable WHERE $dbIdCol = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_STR);

            $stmt->execute();
            // it is recommended to use fetchAll so that PDO can close the DB cursor
            // we anyway expect either no rows, or one row with one column. fetchColumn, seems to be buggy #4777
            $sessionRows = $stmt->fetchAll(\PDO::FETCH_NUM);

            //Remove sessions of kicked users
            foreach($sessionRows as $row){
              if($row[1]==1){
                $stmt = $this->pdo->prepare(
                    "DELETE FROM $dbTable WHERE $dbIdCol = :id"
                );
                $stmt->bindValue(':id', $row[2], \PDO::PARAM_STR);
                $stmt->execute();
                $this->createNewSession($id);
                return '';
              }
            }

            if (count($sessionRows) == 1) {
                return base64_decode($sessionRows[0][0]);
            }

            // session does not exist, create it
            $this->createNewSession($id);

            return '';
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to read the session data: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write($id, $data)
    {
        // get table/column
        $dbTable               = $this->dbOptions['db_table'];
        $dbDataCol             = $this->dbOptions['db_data_col'];
        $dbIdCol               = $this->dbOptions['db_id_col'];
        $dbTimeCol             = $this->dbOptions['db_time_col'];
        $dbUserIdCol           = $this->dbOptions['db_user_id_col'];
        $dbIpaddressCol        = $this->dbOptions['db_ipaddress_col'];
        $dbStartCol            = $this->dbOptions['db_start_col'];
        $dbLastactivityCol     = $this->dbOptions['db_lastactivity_col'];

        //session data can contain non binary safe characters so we need to encode it
        $encoded = base64_encode($data);

        $userId = ($this->tokenStorage->getToken() && is_object($this->tokenStorage->getToken()->getUser())) ? $this->tokenStorage->getToken()->getUser()->getId():null;
        $userId = $userId?intval($userId):null;
        try {
            $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
                // MySQL would report $stmt->rowCount() = 0 on UPDATE when the data is left unchanged
                // it could result in calling createNewSession() whereas the session already exists in
                // the DB which would fail as the id is unique

                if(in_array($_SERVER['REQUEST_URI'],$this->uriExceptions)){
                  $stmt = $this->pdo->prepare(
                      "INSERT INTO $dbTable ($dbIdCol, $dbDataCol, $dbTimeCol, $dbUserIdCol, $dbIpaddressCol, $dbStartCol, $dbLastactivityCol) VALUES (:id, :data, :time, :user_id, :ipaddress, :start, :lastactivity) " .
                      "ON DUPLICATE KEY UPDATE $dbDataCol = VALUES($dbDataCol), $dbTimeCol = VALUES($dbTimeCol), $dbUserIdCol = VALUES($dbUserIdCol)"
                  );
                }else{
                  $stmt = $this->pdo->prepare(
                      "INSERT INTO $dbTable ($dbIdCol, $dbDataCol, $dbTimeCol, $dbUserIdCol, $dbIpaddressCol, $dbStartCol, $dbLastactivityCol) VALUES (:id, :data, :time, :user_id, :ipaddress, :start, :lastactivity) " .
                      "ON DUPLICATE KEY UPDATE $dbDataCol = VALUES($dbDataCol), $dbTimeCol = VALUES($dbTimeCol), $dbUserIdCol = VALUES($dbUserIdCol), $dbLastactivityCol = VALUES($dbLastactivityCol)"
                  );
                }
                $stmt->bindParam(':id', $id, \PDO::PARAM_STR);
                $stmt->bindParam(':data', $encoded, \PDO::PARAM_STR);
                $stmt->bindValue(':time', time(), \PDO::PARAM_INT);
                $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':ipaddress', $_SERVER['REMOTE_ADDR'], \PDO::PARAM_STR);
                $stmt->bindValue(':start', (new \Datetime())->format("Y-m-d H:i:s"), \PDO::PARAM_STR);
                $stmt->bindValue(':lastactivity', (new \Datetime())->format("Y-m-d H:i:s"), \PDO::PARAM_STR);
                $stmt->execute();

                //if userid is not null delete inecesary anonimous sessions
                //if($userId){
                  $stmt = $this->pdo->prepare(
                      "DELETE FROM $dbTable WHERE $dbIpaddressCol = :ipaddress and ($dbDataCol is null or $dbDataCol='')"
                  );
                  $stmt->bindValue(':ipaddress', $_SERVER['REMOTE_ADDR'], \PDO::PARAM_STR);
                  $stmt->execute();
                //}


        } catch (\PDOException $e) {
                throw new \RuntimeException(sprintf('PDOException was thrown when trying to write the session data: %s', $e->getMessage()), 0, $e);
        }

        return true;
    }

    private function createNewSession($id, $data = '')
    {
        // get table/column
        $dbTable               = $this->dbOptions['db_table'];
        $dbDataCol             = $this->dbOptions['db_data_col'];
        $dbIdCol               = $this->dbOptions['db_id_col'];
        $dbTimeCol             = $this->dbOptions['db_time_col'];
        $dbUserIdCol           = $this->dbOptions['db_user_id_col'];
        $dbIpaddressCol        = $this->dbOptions['db_ipaddress_col'];
        $dbStartCol            = $this->dbOptions['db_start_col'];
        $dbLastactivityCol     = $this->dbOptions['db_lastactivity_col'];

        $userId = ($this->tokenStorage->getToken() && is_object($this->tokenStorage->getToken()->getUser())) ? $this->tokenStorage->getToken()->getUser()->getId():null;
        $userId = $userId?intval($userId):null;
        $sql = "INSERT INTO $dbTable ($dbIdCol, $dbDataCol, $dbTimeCol, $dbUserIdCol, $dbIpaddressCol, $dbStartCol, $dbLastactivityCol) VALUES (:id, :data, :time, :user_id, :ipaddress, :start, :lastactivity)";

        //session data can contain non binary safe characters so we need to encode it
        $encoded = base64_encode($data);
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_STR);
        $stmt->bindParam(':data', $encoded, \PDO::PARAM_STR);
        $stmt->bindValue(':time', time(), \PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':ipaddress', $_SERVER['REMOTE_ADDR'], \PDO::PARAM_STR);
        $stmt->bindValue(':start', (new \Datetime())->format("Y-m-d H:i:s"), \PDO::PARAM_STR);
        $stmt->bindValue(':lastactivity', (new \Datetime())->format("Y-m-d H:i:s"), \PDO::PARAM_STR);
        $stmt->execute();
        return true;
    }
}
