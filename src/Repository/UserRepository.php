<?php
namespace Repository;
use Config\Database;
use Entity\User;
use PDO;
use PDOException;

class userRepository
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
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
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