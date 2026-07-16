<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Application;
use app\core\exceptions\ForbiddenException;
use app\core\exceptions\NotFoundException;
use app\core\Request;
use app\models\Course;
use app\models\CourseOnWeb;
use app\models\CourseCategory;
use app\core\form\DBTable;
use app\core\db\DBModel;
use app\core\View;
use app\core\Util;

/**
 * Controller for ITDLH Kelaniya landing and related pages.
 */
class ItdlhController extends Controller
{
        public string $id = 'Itdlh';
    
        public function __construct() {
            // Protect download routes with authentication middleware
            $this->setMiddleware(new \app\core\middlewares\AuthMiddleware([
                'download',
                'add_courses',
                'edit_courses',
                'app_home',
            ]));
        }
    /**
     * Show the ITDLH landing page.
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        return $this->render('itdlh_landing');
    }

    /**
     * Optional endpoint to list courses as JSON (API example).
     *
     * @param Request $request
     * @return string JSON
     */
    public function courses(Request $request)
    {
        $courses = [
            'MS Office Applications',
            'Web Designing',
            'Programming with Python',
            'Programming with Java',
            'Programming with PHP',
            'Graphic Designing',
            'Practical English',
        ];
        header('Content-Type: application/json');
        return json_encode($courses);
    }

    /**
     * Show a single course page based on the slug in the URL.
     * Expected paths: /itdlh/course/{slug}
     *
     * @param Request $request
     * @return string
     */
    public function show_web_course(Request $request)
    {
        $path = $request->getPath();
        $parts = explode('/', trim($path, '/'));
        $slug = end($parts);
        //$slug = $request->getBody()['id'];
    
        $webCourse = new CourseOnWeb();
        $thisCourse = $webCourse->findOne(['course_id' => $slug, 'is_active' => 1]);
        
        $contents = explode(";", $thisCourse->course_contents);
        $map = [
            $slug => [
                    'title' => $thisCourse->course_name,
                    'description' => $thisCourse->course_description,
                    'fee' => 'Rs. ' . number_format($thisCourse->course_fee, 2, '.', ','),
                    'image' => (new View())->asset($thisCourse->course_image_detail),
                    'instructor' => $thisCourse->course_instructor,
                    'contents' => $contents,
                    'duration' => $thisCourse->course_duration,
                    'certification' => $thisCourse->extra,
            ],
        ];

        $course = $map[$slug] ?? null;
        if (!$course) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            return \app\core\Application::$app->router->renderView('_404');
        }
        $this->setLayout('itdlh_landing_new');
        return $this->render('course_on_web_detail_new', ['course' => $course, 'slug' => $slug]);
    }

    /**
     * Serve a protected course PDF download.
     * Expected path: /itdlh/course/{slug}/download
     * Only authenticated users may download.
     *
     * @param Request $request
     */
    public function download(Request $request)
    {
        // Determine slug from URL parts (second-to-last segment).
        $path = $request->getPath();
        $parts = explode('/', trim($path, '/'));
        // e.g. ['itdlh','course','mso','download'] => slug is at -2
        if (count($parts) < 3) {
            throw new NotFoundException();
        }
        $slug = $parts[count($parts) - 2];

        // Require authentication - redirect guests to login page
        if (Application::isGuest()) {
            Application::$app->response->redirect('/login');
            return;
        }

        $baseDir = Application::$ROOT_DIR . '/storage/course-content/';

        // Prefer single PDF named {slug}.pdf
        $storagePath = $baseDir . $slug . '.pdf';
        if (file_exists($storagePath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($storagePath) . '"');
            header('Content-Length: ' . filesize($storagePath));
            readfile($storagePath);
            exit;
        }

        // If there's a directory with multiple files (e.g. storage/course-content/mso/*), create a zip and serve it
        $dirPath = $baseDir . $slug;
        if (is_dir($dirPath)) {
            // Require PHP Zip extension for zipping directories
            if (!class_exists('ZipArchive')) {
                throw new \Exception('Server configuration error: PHP Zip extension (ZipArchive) is not available. Enable the zip extension or place a single PDF at storage/course-content/' . $slug . '.pdf', 501);
            }

            $zipName = Application::$ROOT_DIR . '/runtime/' . $slug . '-content.zip';
            // Create zip archive
            $zip = new \ZipArchive();
            if ($zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath));
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $localPath = substr($filePath, strlen($dirPath) + 1);
                        $zip->addFile($filePath, $localPath);
                    }
                }
                $zip->close();

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipName) . '"');
                header('Content-Length: ' . filesize($zipName));
                readfile($zipName);
                // Remove temporary zip
                @unlink($zipName);
                exit;
            }
        }

        throw new NotFoundException();
    }

    public function upload_file($file_name, $upload_path, $allowed = ['jpg', 'jpeg', 'png', 'pdf'])
    {
        return Util::uploadFiles()->upload_file($file_name, $upload_path, $allowed);
    }

    private function check_upload($model){
        $file_land = $this->upload_file('course_image_file_land',"public/images/courses", ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if($file_land[0]){
                $model->course_image_land = substr($file_land[1],strlen("public"));
            }
            
            $file_detail = $this->upload_file('course_image_file_detail',"public/images/courses", ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if($file_detail[0]){
                    $model->course_image_detail = substr($file_detail[1],strlen("public"));
            }

            $upload = $file_land[0] && $file_detail[0] ;
            $upload = $upload || ($file_land[2] == Util::uploadFiles()::FILE_NOFILE_UPLODED ) && ($file_detail[2] == Util::uploadFiles()::FILE_NOFILE_UPLODED );
            $upload = $upload || ($file_land[0] && ($file_detail[2] == Util::uploadFiles()::FILE_NOFILE_UPLODED ));
            $upload = $upload || ($file_detail[0] && ($file_landLand[2] == Util::uploadFiles()::FILE_NOFILE_UPLODED ));

       
            if(!$upload){
                $land_wrong_file  = ($file_land[2] == Util::uploadFIles()::FILE_INVALID_EXT || $file_land[2] == Util::uploadFIles()::FILE_MISSING_EXT);
                $model->course_image_land = ($land_wrong_file)? $file_land[1] : $model->course_image_land;
                $detail_wrong_file  = ($file_detail[2] == Util::uploadFIles()::FILE_INVALID_EXT || $file_detail[2] == Util::uploadFIles()::FILE_MISSING_EXT);
                $model->course_image_detail = ($detail_wrong_file)? $file_detail[1] : $model->course_image_detail;
                Application::$app->session->setFlash('error', 'files could not be uploaded');
            }
        return $upload;
    }
    
    public function add_courses(Request $request)
    {
        $model = new CourseOnWeb();
        $page = intval($request->getValues()['get']['page'] ?? 1);
        $dataTable = new DBTable($model, $page, 25);

        $dataTable->updateUrl('/public/courses/edit?id={id}&page=' . $page, '/public/courses/delete?id={id}&page=' . $page,'/public/courses/view?id={id}&page=' . $page);
        if ($request->isPost()) {
            $model->loadData($request->getBody());

            $upload = $this->check_upload($model);

            if ($model->validate() && $model->save() && $upload) {
                Application::$app->session->setFlash('success', 'Coure Added Successfully');
                Application::$app->response->redirect('/public/courses/add');
                return;
            }else{
                Application::$app->session->setFlash('error', 'Error adding data to database');
            }
            if(!$upload){
                Application::$app->session->setFlash('error', 'files could not be uploaded');
            }
        }
        $category = new CourseCategory();
        $options = [];
        $allCategory = $category->findAll();
        foreach($allCategory as $cat){
            $options[$cat->category_id] = $cat->category_name;
        }
        $this->setLayout('itdlh_landing_new');
        return $this->render('add_courses', [
            'model' => $model, 'dataTable' => $dataTable, 'categoryOptions' => $options,
        ]);
    }

    public function edit_courses(Request $request)
    {
        $id = $request->getValues()['get']['id'];
        $page = $request->getValues()['get']['page'] ?? 1;
        $newId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $model = (new CourseOnWeb())::findOne(['course_id'=>$newId]);
  
        $dataTable = new DBTable($model, $page, 25);
        $dataTable->updateUrl('/public/courses/edit?id={id}&page=' . $page, '/public/courses/delete?id={id}&page=' . $page,'/public/courses/view?id={id}&page=' . $page);
        if ($request->isPost()) {
            $model->loadData($request->getBody());
            
            $upload = $this->check_upload($model);

            if ($model->validate() && $model->update(['course_id' => $newId]) && $upload) {
                Application::$app->session->setFlash('success', 'Coure Edited Successfully');
                Application::$app->response->redirect('/public/courses/edit?id=' . $id . "&page=" . $page);
                return;
            }else{
                Application::$app->session->setFlash('error', 'Error adding data to database');
            }
            if(!$upload){
                Application::$app->session->setFlash('error', 'files could not be uploaded');
            }
        }
        $category = new CourseCategory();
        $options = [];
        $allCategory = $category->findAll();
        foreach($allCategory as $cat){
            $options[$cat->category_id] = $cat->category_name;
        }
        $this->setLayout('itdlh_landing_new');
        return $this->render('add_courses', [
            'model' => $model, 'dataTable' => $dataTable, 'categoryOptions' => $options,
        ]);
    }

    public function add_category(Request $request){
        $model = new CourseCategory();
        $page = intval($request->getBody()['page'] ?? 1);


        
        $dataTable = new DBTable($model, $page, 25);
        $dataTable->updateUrl('courses/catetory/edit?id={id}&page=' . $page, 'courses/caterory/delete?id={id}&page=' . $page,'courses/category/view?id={id}&page=' . $page);
        if ($request->isPost()) {
            $model->loadData($request->getBody());
            
            if ($model->validate() && $model->save()) {
                Application::$app->response->redirect('/public/courses/category/add');
                return;
            }
        }
        $this->setLayout('itdlh_landing_new');
        return $this->render('itdlh_add_course_category', [
            'model' => $model, 'dataTable' => $dataTable,
        ]);
    }
    public function edit_category(Request $request){
        $model = new CourseCategory();
        $page = intval($request->getBody()['page'] ?? 1);
        
        $dataTable = new DBTable($model, $page, 25);
        $dataTable->updateUrl('courses/catetory/edit?id={id}&page=' . $page, 'courses/caterory/delete?id={id}&page=' . $page,'courses/category/view?id={id}&page=' . $page);
        if ($request->isPost()) {
            $model->loadData($request->getBody());
            
            if ($model->validate() && $model->save()) {
                Application::$app->response->redirect('/public/courses/category/add');
                return;
            }
        }
        $this->setLayout('itdlh_landing_new');
        return $this->render('itdlh_add_course_category', [
            'model' => $model, 'dataTable' => $dataTable,
        ]);
    }

    public function app_home(Request $request){
        $isAdmin = Application::isAdmin();
        $this->setLayout('itdlh_landing_new');
        return $this->render('app_new', [
            'isAdmin' => $isAdmin,
        ]);
    }
}
