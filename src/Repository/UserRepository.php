<?php
namespace PharmaFEFO\Repository;
use PDO;
use PDOException;
use PharmaFEFO\config\Database;
use PharmaFEFO\Entity\User;

class UserRepository
{
    private PDO $pdo;
    public function __construct(){
        $this->pdo = Database::getConnection();
    }

    public function verifyLogin($email,$password):?User{
        try{
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$user){
                return null;
            }
            if(password_verify($password,$user['password'])){
                return new User(
                    $user['firstname'],
                    $user['lastname'],
                    $user['email'],
                    $user['role'],
                    $user['id']
                );
            } else {
                return null;
            }
        }catch(PDOException $e){
            echo "Error :".$e->getMessage();
            return null;
        }

    }
}