<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Http\Request;
use App\Models\Account;
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserDo extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    //Regist Session
    public function regist() {
        return view("regist");
    }

    public function signup(Request $req){
        $req->validate(['Password' => 'required|confirmed|min:6']);
        $account = new Account;
        $account->Email = $req->Email;
        $account->Number = $req->Number;
        $account->Password = sha1($req->Password);
        $account->Photo = "";
        $account->save();

        require base_path("vendor/autoload.php");
        $PASS = $_ENV['PASS'];
        $MAIL = $_ENV['EMAIL'];
        $judul = "Your OTP Code is Here!";
        $pesan = rand(100000, 999999);
        $email = $req->Email;
        $mail = new PHPMailer(true);     // Passing `true` enables exceptions
 
        try {
 
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $MAIL;                     //SMTP username
            $mail->Password   = $PASS;                               //SMTP password
            $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
            //pengirim
            $mail->setFrom('email@gmail.com', 'Your OTP Code is Here!');
            $mail->addAddress($email);     //Add a recipient
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $judul;
            $mail->Body    = $pesan;
            $mail->AltBody = '';
            //$mail->AddEmbeddedImage('gambar/logo.png', 'logo'); //abaikan jika tidak ada logo
            //$mail->addAttachment(''); 
    
            $mail->send();
        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    
        }

        return redirect('/login');
    }

    //Login Session
    public function login(){
        $var=session('account');
        if($var == null){
            return view("login");
        }
        return redirect('/');
    }

    public function signin(request $req) {
        $account = Account::where("Email", $req->Email)->where("Password",sha1($req->Password))->first();
        if ($account == null){
            $account = Account::where("Username", $req->Email)->where("Password",sha1($req->Password))->first();
        }
        if(!empty($account)){
            if ($account->Email == "admin@gmail.com" || $account->Username == "admin"){
                $account->Role = 'admin';
                session(['account'=>$account]);
                return redirect('/admin');
            } else {
                $account->Role = 'customer';
                session(['account'=>$account]);
                return redirect('/');
            }
        } else {
            return redirect("/")->with("alert","User tidak ditemukan !!!");
        }
    }

    //Logout Session
    public function signout(){
        session(['account'=>null]);
        return redirect('/');
    }

}