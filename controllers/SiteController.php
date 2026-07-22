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
        $map = [
            'mso' => [
                    'title' => 'MS Office Applications',
                    'description' => 'Comprehensive, practical training on Microsoft Office suite and essential desktop skills for workplace productivity.',
                    'fee' => 'Rs. 7,500.00',
                    'image' => '/images/courses/mso.png',
                    'instructor' => 'L.A.S. Kumarasinghe',
                    'contents' => [
                        'Introduction',
                        'Windows OS',
                        'MS Word',
                        'MS Excel',
                        'MS PowerPoint',
                        'MS Access',
                        'Internet And Email',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'web' => [
                'title' => 'Web Designing',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Learn HTML, CSS, responsive design and modern frontend tooling to build attractive websites.',
                'image' => '/images/courses/web.webp',
                'instructor' => 'B.D.P. Niranjan',
                'contents' => [
                        'Introduction',
                        'HTML 5.0',
                        'CSS 3.0',
                        'JavaScript',
                        'Bootstrap',
                        'wordpress',
                        'Responsive Design',
                        'Modern Frontend Tooling'
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'python' => [
                'title' => 'Programming with Python',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Intro to Python programming, data structures, scripting and practical projects.',
                'image' => '/images/courses/python.webp',
                'instructor' => 'B.D.P. Niranjan',
                'contents' => [
                        'Introduction to Programming',
                        'Python Basics',
                        'OOP with Python',
                    
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'java' => [
                'title' => 'Programming with Java',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Object-oriented programming, Java fundamentals and application development.',
                'image' => '/images/courses/java.jpg',
                'instructor' => 'B.D.P. Niranjan',
                'contents' => [
                        'Introduction to Programming',
                        'Java Basics',
                        'OOP with Java',
                        'Java Application Development',
                        'connecting Java with Databases',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            
            'php' => [
                'title' => 'Programming with PHP',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Server-side web development with PHP: building dynamic websites and simple APIs.',
                'image' => '/images/courses/php.jpg',
                'instructor' => 'B.D.P. Niranjan',
                'contents' => [
                        'Introduction to PHP',
                        'PHP Basics',
                        'OOP with PHP',
                        'Building Dynamic Websites',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'graphic' => [
                'title' => 'Graphic Designing',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Design fundamentals, image editing and tools like Photoshop and Illustrator.',
                'image' => '/images/courses/graphic.jpg',
                'instructor' => 'T.K.C Talpavila',
                'contents' => [
                        'Design Fundamentals',
                        'Image Editing',
                        'Photoshop',
                        'Illustrator',
                        'CorelDraw',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'english' => [
                'title' => 'Practical English',
                'fee' => 'Rs. 6,000.00',
                'description' => 'Communicative English for students focusing on workplace and technical communication.',
                'image' => '/images/courses/english.jpg',
                'instructor' => 'R.T.S. Ranasinghe',
                'contents' => [
                        'Grammar Basics',
                        'Workplace Communication',
                        'Technical Writing',
                        'Presentation Skills',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
        ];

        return $this->renderViewOnly('mandakini_landing', ['courses' => $map]);
    }

    public function contact(Request $request) {
        $contactForm = new ContactForm();
        $this->setLayout('mandakini_layout');
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
        $this->setLayout('mandakini_layout');
        return $this->render('about');
    }

    public function staff(Request $request){
        $this->setLayout('mandakini_layout');
        return $this->render('staff');
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

        $this->setLayout('mandakini_layout');
        return $this->render('staff_details', ['member' => $staff[$id]]);
    }

    public function courses(Request $request) {
        $courseId = $request->getPath(); // Get the full path
        $courseId = basename($courseId); // Extract the last part of the path as the course ID

        $courses = [
            'mso' => [
                'title' => 'MS Office Applications',
                'description' => 'Comprehensive, practical training on Microsoft Office suite and essential desktop skills for workplace productivity.',
                'fee' => 'Rs. 7,500.00',
                'image' => '/images/courses/mso.png',
                'instructor' => 'L.A.S. Kumarasinghe',
                'contents' => [
                    'Introduction',
                    'Windows OS',
                    'MS Word',
                    'MS Excel',
                    'MS PowerPoint',
                    'MS Access',
                    'Internet And Email',
                ],
                'duration' => '120 hours',
                'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
           'web' => [
                'title' => 'Web Designing',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Learn HTML, CSS, responsive design and modern frontend tooling to build attractive websites.',
                'image' => '/images/courses/web.webp',
                'instructor' => 'B.D.P. Niranjan',
                'contents' => [
                        'Introduction',
                        'HTML 5.0',
                        'CSS 3.0',
                        'JavaScript',
                        'Bootstrap',
                        'wordpress',
                        'Responsive Design',
                        'Modern Frontend Tooling'
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'python' => [
                'title' => 'Programming with Python',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Intro to Python programming, data structures, scripting and practical projects.',
                'image' => '/images/courses/python.webp',
                'instructor' => 'B.D.P. Niranjan',
                'contents' => [
                        'Introduction to Programming',
                        'Python Basics',
                        'OOP with Python',
                    
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'java' => [
                'title' => 'Programming with Java',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Object-oriented programming, Java fundamentals and application development.',
                'image' => '/images/courses/java.jpg',
                'instructor' => 'B.D.P. Niranjan',
                'contents' => [
                        'Introduction to Programming',
                        'Java Basics',
                        'OOP with Java',
                        'Java Application Development',
                        'connecting Java with Databases',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            
            'php' => [
                'title' => 'Programming with PHP',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Server-side web development with PHP: building dynamic websites and simple APIs.',
                'image' => '/images/courses/php.jpg',
                'instructor' => 'B.D.P. Niranjan',
                'contents' => [
                        'Introduction to PHP',
                        'PHP Basics',
                        'OOP with PHP',
                        'Building Dynamic Websites',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'graphic' => [
                'title' => 'Graphic Designing',
                'fee' => 'Rs. 7,500.00',
                'description' => 'Design fundamentals, image editing and tools like Photoshop and Illustrator.',
                'image' => '/images/courses/graphic.jpg',
                'instructor' => 'T.K.C Talpavila',
                'contents' => [
                        'Design Fundamentals',
                        'Image Editing',
                        'Photoshop',
                        'Illustrator',
                        'CorelDraw',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
            'english' => [
                'title' => 'Practical English',
                'fee' => 'Rs. 6,000.00',
                'description' => 'Communicative English for students focusing on workplace and technical communication.',
                'image' => '/images/courses/english.jpg',
                'instructor' => 'R.T.S. Ranasinghe',
                'contents' => [
                        'Grammar Basics',
                        'Workplace Communication',
                        'Technical Writing',
                        'Presentation Skills',
                    ],
                    'duration' => '120 hours',
                    'certification' => 'Certification issued under the Ministry of Education of Sri Lanka',
            ],
        ];

        if (!isset($courses[$courseId])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            return $this->render('_404');
        }

        $this->setLayout('mandakini_layout');
        return $this->render('courses', ['course' => $courses[$courseId]]);
    }

   
}

