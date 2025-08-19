<?php

namespace App\DataFixtures;

use App\Document\ThemeDocument;
use App\Document\CourseDocument;
use App\Document\LessonDocument;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixturesMongo extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $theme1 = new ThemeDocument();
        $theme1->setName('musique');
        $manager->persist($theme1);

        $course1 = new CourseDocument();
        $course1->setTitle('cursus d’initiation à la guitare ');
        $course1->setPrice(50.00);
        $course1->setTheme($theme1);
        $manager->persist($course1);

        $lesson1 = new LessonDocument();
        $lesson1->setTitle("leçon n°1 : Découverte de l'instrument");
        $lesson1->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson1->setPrice(26.00);
        $lesson1->setCourse($course1);
        $lesson1->setVideoName(null);
        $manager->persist($lesson1);

        $lesson2 = new LessonDocument();
        $lesson2->setTitle("leçon n°2 : Les accords et les gammes ");
        $lesson2->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson2->setPrice(26.00);
        $lesson2->setCourse($course1);
        $lesson2->setVideoName(null);
        $manager->persist($lesson2);

        $course2 = new CourseDocument();
        $course2->setTitle('cursus d’initiation au piano');
        $course2->setPrice(50.00);
        $course2->setTheme($theme1);
        $manager->persist($course2);

        $lesson3 = new LessonDocument();
        $lesson3->setTitle("leçon n°1 : Découverte de l'instrument");
        $lesson3->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson3->setPrice(26.00);
        $lesson3->setCourse($course2);
        $lesson3->setVideoName(null);
        $manager->persist($lesson3);

        $lesson4 = new LessonDocument();
        $lesson4->setTitle("leçon n°2 : Les accords et les gammes ");
        $lesson4->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson4->setPrice(26.00);
        $lesson4->setCourse($course2);
        $lesson4->setVideoName(null);
        $manager->persist($lesson4);



        $theme2 = new ThemeDocument();
        $theme2->setName('informatique');
        $manager->persist($theme2);

        $course3 = new CourseDocument();
        $course3->setTitle('cursus d’initiation au développement web');
        $course3->setPrice(60.00);
        $course3->setTheme($theme2);
        $manager->persist($course3);

        $lesson5 = new LessonDocument();
        $lesson5->setTitle('leçon n°1 : Les langages Html et CSS');
        $lesson5->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson5->setPrice(32.00);
        $lesson5->setCourse($course3);
        $lesson5->setVideoName(null);
        $manager->persist($lesson5);

        $lesson6 = new LessonDocument();
        $lesson6->setTitle('leçon n°2 : Dynamiser votre site avec Javascript');
        $lesson6->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson6->setPrice(32.00);
        $lesson6->setCourse($course3);
        $lesson6->setVideoName(null);
        $manager->persist($lesson6);




        $theme3 = new ThemeDocument();
        $theme3->setName('lardinage ');
        $manager->persist($theme3);

        $course4 = new CourseDocument();
        $course4->setTitle('cursus d’initiation au jardinage');
        $course4->setPrice(30.00);
        $course4->setTheme($theme3);
        $manager->persist($course4);

        $lesson7 = new LessonDocument();
        $lesson7->setTitle("leçon n°1 : Les outils du jardinier");
        $lesson7->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson7->setPrice(16.00);
        $lesson7->setCourse($course4);
        $lesson7->setVideoName(null);
        $manager->persist($lesson7);

        $lesson8 = new LessonDocument();
        $lesson8->setTitle("leçon n°2 : Jardiner avec la lune");
        $lesson8->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson8->setPrice(16.00);
        $lesson8->setCourse($course4);
        $lesson8->setVideoName(null);
        $manager->persist($lesson8);


        $theme4 = new ThemeDocument();
        $theme4->setName('cuisine');
        $manager->persist($theme4);

        $course5 = new CourseDocument();
        $course5->setTitle('cursus d’initiation à la cuisine');
        $course5->setPrice(44.00);
        $course5->setTheme($theme4);
        $manager->persist($course5);

        $lesson9 = new LessonDocument();
        $lesson9->setTitle("leçon n°1 : Les modes de cuisson");
        $lesson9->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson9->setPrice(23.00);
        $lesson9->setCourse($course5);
        $lesson9->setVideoName(null);
        $manager->persist($lesson9);

        $lesson10 = new LessonDocument();
        $lesson10->setTitle("leçon n°2 : Les saveurs");
        $lesson10->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson10->setPrice(23.00);
        $lesson10->setCourse($course5);
        $lesson10->setVideoName(null);
        $manager->persist($lesson10);

        $course6 = new CourseDocument();
        $course6->setTitle('cursus d’initiation à l’art du dressage culinaire');
        $course6->setPrice(48.00);
        $course6->setTheme($theme4);
        $manager->persist($course6);

        $lesson11 = new LessonDocument();
        $lesson11->setTitle("leçon n°1 : Mettre en œuvre le style dans l’assiette");
        $lesson11->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson11->setPrice(26.00);
        $lesson11->setCourse($course6);
        $lesson11->setVideoName(null);
        $manager->persist($lesson11);

        $lesson12 = new LessonDocument();
        $lesson12->setTitle("leçon n°2 : Harmoniser un repas à quatre plats");
        $lesson12->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam turpis diam, suscipit at urna a, lobortis euismod lectus. Fusce pellentesque leo massa, et pretium tortor interdum sed. Vestibulum metus lacus, blandit a augue non, pulvinar sollicitudin nibh. Praesent tincidunt mi vel nulla gravida, vel finibus est cursus. Proin tempus lectus a gravida mollis. Sed posuere tristique pharetra. Sed et elementum magna. Morbi nec dolor elementum, laoreet mauris et, hendrerit arcu. Fusce cursus egestas lacus. Aenean id nisl at elit hendrerit ultricies vitae ac libero. Vestibulum sed consectetur est.
        Nulla facilisi. Maecenas volutpat sed orci ut accumsan. Aenean eleifend lorem eu fringilla bibendum. Quisque posuere est nibh. Vivamus arcu dui, feugiat non lorem suscipit, consectetur fermentum massa. Morbi porttitor tempor libero eget tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas a consequat nulla.
        Phasellus vel augue malesuada, semper arcu vel, bibendum justo. Nunc quis maximus leo. Fusce egestas lacinia malesuada. Phasellus vitae facilisis elit, ut condimentum sem. Nullam et eros est. Fusce vehicula arcu quis augue consequat, nec cursus dui porta. Praesent a diam lacus. Integer lobortis consequat quam, a auctor risus efficitur eu. Integer consectetur scelerisque est, ut accumsan sem.
        In accumsan sed odio rutrum laoreet. Curabitur nisl lacus, egestas eget velit sed, blandit fermentum eros. Praesent bibendum aliquam ullamcorper. Sed sit amet nunc sed nisi scelerisque ultricies. Nulla pulvinar blandit consectetur. Curabitur hendrerit eros quis orci rutrum, in egestas velit sodales. Aenean quis velit in odio porttitor laoreet at sit amet purus. Sed nibh erat, iaculis eu libero rutrum, bibendum rutrum arcu.
        Nullam iaculis porta nulla, ac fermentum turpis commodo ac. Nam porta vehicula tortor et gravida. Nunc hendrerit tortor orci, et elementum ipsum lobortis in. Sed in nibh in augue lobortis placerat et non nisl. Vivamus condimentum auctor purus vitae tincidunt. Vivamus neque ligula, bibendum at consectetur sed, ultricies et eros. Fusce sodales ex lacus, id blandit ante elementum vitae. Aliquam cursus velit risus, sed vestibulum lectus malesuada et. Proin eget pellentesque velit, eu elementum eros. Sed semper tortor ut nisi volutpat, non dignissim mi iaculis. Sed feugiat tempor augue, tincidunt venenatis nunc tincidunt at. Fusce vel sodales mauris. Sed nunc lacus, fringilla in justo suscipit, lobortis tincidunt ex.');
        $lesson12->setPrice(26.00);
        $lesson12->setCourse($course6);
        $lesson12->setVideoName(null);
        $manager->persist($lesson12);

        $manager->flush();
    }
}
