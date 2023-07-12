<?php
/**
 * MIT states: "The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software."
 *
 * GPL states: "You must cause the modified files to carry prominent notices stating
 * that you changed the files and the date of any change."
 *
 *
 * @author BrianHenryIE
 * @author Alex Jurii
 *
 * @license MIT
 */

namespace AlexLabs\Strauss\Console;

use AlexLabs\Strauss\Console\Commands\Compose;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * @param string $version
     */
    public function __construct(string $version)
    {
        parent::__construct('strauss', $version);

        $composeCommand = new Compose();
        $this->add($composeCommand);

        $this->setDefaultCommand('compose');
    }
}
