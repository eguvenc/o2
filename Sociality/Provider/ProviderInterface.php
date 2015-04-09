<?php

namespace Obullo\Sociality\Provider;

interface ProviderInterface
{
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return void
     */
    public function redirect();

    /**
     * Returns redirect uri
     *
     * @return string
     */
    public function redirectOutput();
}

// END ProviderInterface.php File
/* End of file ProviderInterface.php

/* Location: .Obullo/Sociality/Provider/ProviderInterface.php */
