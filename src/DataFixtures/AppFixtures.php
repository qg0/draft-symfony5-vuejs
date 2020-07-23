<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

namespace App\DataFixtures;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Status;
use App\Entity\Document;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AppFixtures.
 *
 * Create fixtures
 */
class AppFixtures extends Fixture
{
    /**
     * Password encoder.
     */
    private UserPasswordEncoderInterface $encoder;

    /**
     * AppFixtures constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * Load data.
     *
     * @param ObjectManager $manager
     *
     * @throws Exception
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $userAdmin = new User();
        $userAdmin->setLogin('admin');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $password = $this->encoder->encodePassword($userAdmin, 'admin');
        $userAdmin->setPassword($password);
        $userAdmin->setToken(hash('sha512', random_bytes(0xFFFF)));
        $userAdmin->setUntil(new DateTime('now + 1 year'));
        $manager->persist($userAdmin);

        $userUser = new User();
        $userUser->setLogin('user');
        $userUser->setRoles(['ROLE_USER']);
        $password = $this->encoder->encodePassword($userUser, 'user');
        $userUser->setPassword($password);
        $userUser->setToken(hash('sha512', random_bytes(0xFFFF)));
        $userUser->setUntil(new DateTime('now + 1 day'));
        $manager->persist($userUser);

        $statusPublished = new Status();
        $statusPublished->setTitle('published');
        $manager->persist($statusPublished);

        $statusDraft = new Status();
        $statusDraft->setTitle('draft');
        $manager->persist($statusDraft);

        foreach ([$userAdmin, $userUser] as $user) {
            foreach ([$statusPublished, $statusDraft] as $status) {
                for ($i = 0; $i < 50; ++$i) {
                    $document = new Document();
                    $document->setStatus($status);
                    $document->setPayload((object) $this->getPayload($user, $status));
                    $document->setUser($user);

                    $manager->persist($document);
                }
            }
        }

        $manager->flush();
    }

    /**
     * Get the payload.
     *
     * @param User   $user
     * @param Status $status
     *
     * @throws Exception
     *
     * @return array
     */
    protected function getPayload(User $user, Status $status): array
    {
        return [
            'user'   => $user->getLogin(),
            'status' => $status->getTitle(),
            'actor'  => $this->getRandomValue('actor'),
            'meta'   => [
                'type'  => $this->getRandomValue('type'),
                'color' => $this->getRandomValue('color'),
                'level' => $this->getRandomValue('level'),
                'mark'  => $this->getRandomValue('mark'),
                'size'  => $this->getRandomValue('size'),
            ],
            'actions' => [
                [
                    'action' => $this->getRandomValue('action'),
                    'actor'  => $this->getRandomValue('actor'),
                ],
                [
                    'action' => $this->getRandomValue('action'),
                ],
            ],
        ];
    }

    /**
     * Get random value.
     *
     * @param string $name
     *
     * @throws Exception
     *
     * @return string
     */
    protected function getRandomValue($name = 'actor'): string
    {
        $type   = ['cunning', 'snappish', 'running', 'flying', 'jumping', 'sleeping', 'clever', 'stupid', 'awesome'];
        $color  = ['red', 'blue', 'yellow', 'green', 'white', 'black', 'brown', 'pink', 'magenta', 'cyan', 'purple'];
        $level  = ['below', 'above', 'beyond', 'near', 'far', 'close', 'here', 'there', 'normal', 'high', 'super'];
        $mark   = ['zero', 'one', 'two', 'three', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'hundred'];
        $size   = ['tiny', 'small', 'normal', 'big', 'huge', 'middle', 'enormous', 'tall', 'short', 'micro', 'mega'];
        $action = ['eat', 'bite', 'sleep', 'run', 'walk', 'scream', 'sing', 'hear', 'smell', 'cry', 'drink', 'relax'];
        $actor  = ['fox', 'cat', 'dog', 'lion', 'elephant', 'spider', 'monkey', 'parrot', 'eagle', 'bunny', 'flea'];

        return isset($$name) ? $$name[random_int(0, count($$name) - 1)] : '';
    }
}
