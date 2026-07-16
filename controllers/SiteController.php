<?php
namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\core\Response;
use app\models\ContactForm;
use app\models\CourseOnWeb;


class SiteController extends Controller{
    public function home(Request $request) {
        //$this->setLayout('blank-layout');
        $webCourse = new CourseOnWeb();
        $courses = $webCourse->findAll();
        return $this->renderViewOnly('itdlh_landing_new', ['webCourseList' => $courses]);
    }

    public function contact(Request $request) {
        $contactForm = new ContactForm();
        $this->setLayout('itdlh_landing_new');
        if ($request->isPost()) {
            $contactForm->loadData($request->getBody());
            if ($contactForm->validate() && $contactForm->send()) {
                Application::$app->session->setFlash('success', 'Thanks for contacting us!');

                // Process the form data (e.g., save to database, send email, etc.)
                return $this->render('contact', [
                    'message' => 'Form submitted successfully!',
                    'model' => $contactForm,
                ]);
            }
            
             return $this->render('contact', [
                'model' => $contactForm,
            ]);
        }
        return $this->render('contact', [
            'model' => $contactForm,
        ]);
      }

    public function handleContact(Request $request) {
         $body = $request->getBody();
         // Process the form data (e.g., save to database, send email, etc.)
        return $this->render('contact', [
            'message' => 'Form submitted successfully!'
        ]);
    }
    
    public function about(Request $request)
    {
        $this->setLayout('itdlh_landing_new');
        return $this->render('about');
    }

    public function staff(Request $request){
        $this->setLayout('itdlh_landing_new');
        return $this->render('staff_new');
    }

    /**
     * Show a single staff member details page.
     * Expects query parameter `id` matching the zero-based index in the staff array.
     */

    public function staffDetails(Request $request)
    {
        $body = $request->getBody();
        $id = isset($body['id']) ? (int)$body['id'] : null;

        // Updated staff list matching the dynamic view requirements
        $staff = [
            [
                'name' => 'Mr. L.A.S. Kumarasinghe', 
                'role' => 'Center Manager', 
                'bio' => 'Mr. Kumarasinghe serves as the Center Manager at ITDLH Kelaniya, bringing over a decade of administrative leadership and educational expertise to the branch. In addition to supervising daily campus operations, student affairs, and institutional growth, he acts as the primary instructor for Microsoft Office Applications. He is dedicated to bridging the digital divide by equipping adult learners, corporate professionals, and students with fundamental digital literacy skills essential for modern workplace efficiency and career advancement.', 
                'photo' => 'images/staff/person.webp',
                'email' => 'kumarasinghe@itdlh.lk',
                'responsibilities' => [
                    'Overall management and administration of the ITDLH Kelaniya center.',
                    'Curriculum development and quality assurance for office productivity courses.',
                    'Conducting lectures and practical sessions for MS Office Applications.'
                ],
                'expertise' => ['Office Automation', 'Center Management', 'MS Office Suite', 'Student Counseling']
            ],
            [
                'name' => 'Ms. R.T.S. Ranasinge', 
                'role' => 'Instructor', 
                'bio' => 'Ms. Ranasinge is an experienced Language Instructor at ITDLH Kelaniya, pioneering the highly sought-after \'Certification of Practical English\' course. With a profound background in applied linguistics and communicative teaching methodologies, she specializes in breaking down complex grammatical and phonological barriers for ESL (English as a Second Language) students. Her tailored coaching programs focus heavily on improving active corporate communication, public speaking confidence, and professional presentation skills among undergraduate and postgraduate learners.', 
                'photo' => 'images/staff/person.webp',
                'email' => 'ranasinghe.r@itdlh.lk',
                'responsibilities' => [
                    'Delivering course modules for the Certification of Practical English.',
                    'Conducting language labs and speaking assessments.',
                    'Developing student study guides and evaluation materials.'
                ],
                'expertise' => ['Practical English', 'Business Communication', 'Linguistics', 'Curriculum Design']
            ],
            [
                'name' => 'Mr. T.K.C. Talpawila', 
                'role' => 'Instructor', 
                'bio' => 'Mr. Talpawila functions as the Chief Creative Instructor at ITDLH Kelaniya, spearheading the \'Certification of Graphic Design\' curriculum. Combining industry-vetted expertise with a deep artistic vision, he guides students through the principles of visual storytelling, corporate branding, and modern multimedia design assets. His project-based teaching method exposes learners to critical industry platforms, training them to transition from creative concepts into high-value commercially viable digital designs suitable for freelance and agency markets.', 
                'photo' => 'images/staff/person.webp',
                'email' => 'talpawila.t@itdlh.lk',
                'responsibilities' => [
                    'Conducting the Certification of Graphic Design course.',
                    'Guiding students on professional portfolio creation.',
                    'Evaluating design projects and practical examinations.'
                ],
                'expertise' => ['Graphic Design', 'Adobe Photoshop', 'Adobe Illustrator', 'Visual Communication']
            ],
            [
                'name' => 'Mr. B.D.P. Niranjan', 
                'role' => 'Instructor', 
                'bio' => 'Mr. Niranjan is a highly technical Academic Instructor at ITDLH Kelaniya, directing the Software Programming and Web Design engineering paths. Known for his robust logical analysis and modern computing expertise, he mentors students through front-end architectural frameworks and back-end database implementations. His core passion lies in transforming amateur coders into proficient full-stack developers, utilizing iterative hackathons, real-world case studies, and modern clean-coding guidelines to ready them for the competitive technology market.', 
                'photo' => 'images/staff/person.webp',
                'email' => 'niranjan.b@itdlh.lk',
                'responsibilities' => [
                    'Conducting lectures and practical work for Programming and Web Design courses.',
                    'Assisting students with coding projects and logic building.',
                    'Maintaining and updating the lab software configurations.'
                ],
                'expertise' => ['Web Development', 'Programming Logic', 'HTML/CSS/JS', 'PHP & MySQL']
            ],
        ];


        if ($id === null || !isset($staff[$id])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            return $this->render('_404');
        }

        $this->setLayout('itdlh_landing_new');
        return $this->render('staff_details_new', ['member' => $staff[$id]]);
    }

    public function staffDetails_old(Request $request)
    {
        $body = $request->getBody();
        $id = isset($body['id']) ? (int)$body['id'] : null;

        // Same sample staff list as used in the staff view. Keep in sync or replace with model.
        $staff = [
            ['name' => 'Mr. L.A.S. Kumarasinghe', 'role' => 'Center Manager', 'bio' => 'Center Manager of ITDLH Kelaniya and the main instructor for MS Office Applications', 'photo' => 'images/staff/person.webp'],
            ['name' => 'Ms. R.T.S. Ranasinge', 'role' => 'Instructor', 'bio' => 'Instructor of ITDLH Kelaniya and the main instructor for the \'Certification of Practical English\' course', 'photo' => 'images/staff/person.webp'],
            ['name' => 'Mr. T.K.C. Talpawila', 'role' => 'Instructor', 'bio' => 'Instructor of ITDLH Kelaniya and the main instructor for the \'Certification of Graphic Design\' course', 'photo' => 'images/staff/person.webp'],
            ['name' => 'Mr. B.D.P. Niranjan', 'role' => 'Instructor', 'bio' => 'Instructor of ITDLH Kelaniya and the main instructo for Prorgrammin and Web Design Coures', 'photo' => 'images/staff/person.webp'],
        ];

        if ($id === null || !isset($staff[$id])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            return $this->render('_404');
        }

        $this->setLayout('main');
        return $this->render('staff_details', ['member' => $staff[$id]]);
    }
}

