<?php
require_once(PROJECT_ROOT_PATH . '/vendor/autoload.php');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class UserController extends BaseController
{
    /** 
* "/user/list" Endpoint - Get list of users 
*/
    public function authenticate($settings_json) {
        $users = $settings_json->users;
        $json_data = json_decode(file_get_contents('php://input'));
        if (isset($json_data)) {
            if (property_exists($users, $json_data->username)) {
                $username = $json_data->username;
                if (password_verify($json_data->password, $users->$username))
                {
                    $access_token = $this->get_access_token($json_data->username, $settings_json);
                    $response = new stdClass();
                    $response->access_token = $access_token;

                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($response);
                }
                else 
                {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($settings_json->errors->users->request_password_mismatch);
                }
            }
        }
    }
    public function get_access_token($validated_username, $settings_json) {
        $secret_key = $settings_json->secret_key;
        $date   = new DateTimeImmutable();
        $expire_at     = $date->modify('+1 day')->getTimestamp();      // Add 60 seconds
        $domain_name = $settings_json->domain_name;
        $username   = $validated_username;                                           // Retrieved from filtered POST data
        $request_data = [
            'iat'  => $date->getTimestamp(),         // Issued at: time when the token was generated
            'iss'  => $domain_name,                       // Issuer
            'nbf'  => $date->getTimestamp(),         // Not before
            'exp'  => $expire_at,                           // Expire
            'userName' => $username,                     // User name
        ];
        return JWT::encode($request_data, $secret_key, 'HS512');
    }

    public function verify_credentials($settings_json) {
        try {
            $headers = $this->getRequestHeaders();
            $token = explode(" ",$headers["Authorization"])[1];
            $secret_key  = $settings_json->secret_key;
            $token = JWT::decode($token, new Key($secret_key, 'HS512'));
            $now = new DateTimeImmutable();

            if ($token->iss !== $settings_json->domain_name ||
                $token->nbf > $now->getTimestamp() ||
                $token->exp < $now->getTimestamp())
            {
                header('HTTP/1.1 401 Unauthorized');
                exit();
            }

            return true;
        }
        catch (\Exception $e) {
            header("HTTP/1.1 401 Unauthorized");
            exit();
        }
        


    }
}