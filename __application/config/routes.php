<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'backend';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Routing Core
$route['backoffice'] = 'backend';
$route['backoffice-masuk'] = 'login';
$route['backoffice-keluar'] = 'login/logout';
$route['Backoffice-Grid/(:any)/(:any)'] = 'backend/get_grid/$1/$2';
$route['backoffice-GetDataChart'] = 'backend/get_chart';
$route['Backoffice-Status/(:any)'] = 'backend/set_flag/$1';
$route['backoffice-form/(:any)'] = 'backend/get_form/$1';
$route['backoffice-Data/(:any)'] = 'backend/getdata/$1';
$route['backoffice-GetDetil'] = 'backend/get_konten';
$route['backoffice-konten/(:any)'] = 'backend/get_konten/$1';
$route['Backoffice-Report/(:any)'] = 'backend/get_report/$1';
$route['backoffice-simpan/(:any)/(:any)'] = 'backend/simpandata/$1/$2';
$route['beranda'] = 'backend/modul/beranda/main';
$route['registrasi'] = 'login/viewregistrasi';
$route['submit-register'] = 'login/submitregistrasi';
$route['getkab'] = 'login/getcombo/cl_kab_kota';
$route['getkec'] = 'login/getcombo/cl_kecamatan';
$route['backoffice-Cetak'] = 'backend/cetak';

$route['forgotpassword'] = 'login/viewlupapassword';
$route['submit-lupapassword'] = 'login/submitlupapassword';

$route['user-profile'] = 'backend/getdisplay/user_profile';
$route['submit-ubahprofil'] = 'backend/simpandata/update_profile/edit';
$route['ubah-password'] = 'backend/getdisplay/ubah_password';
$route['submit-ubahpassword'] = 'backend/simpandata/ubah_password/edit';
$route['cetak-kartu'] = 'backend/generatepdf/cetak_kartu';




/* Routes Front End Routes */



