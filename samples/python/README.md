# SaxonC Python samples

There are a number of samples provided to show you some of the basics in context.

Once you've installed the SaxonC Python wheel:

    $ pip install saxonche

You can run the samples from inside their directory:

    $ cd samples
    $ python saxon_example2.py

Also provided are the `pytest`-run tests we run ourselves. Because these are
run against every edition (HE, PE, EE) of SaxonC, they're a little more involved 
to run. You need to tell the runner which edition you're using by setting the 
`SAXONC_EDITION` environment variable:

    $ pip install pytest
    $ SAXONC_EDITION=he pytest --import-mode=importlib \
        --data-dir=../samples/data \
        test 

Or, with PowerShell on Windows:

    PS > pip install pytest
    PS > $env:SAXONC_EDITION = "he"
    PS > pytest --import-mode=importlib --data-dir=../samples/data test 

Full documentation for SaxonC 12 Python is available. The [SaxonC Python 12
documentation][1] contains information specific to SaxonC Python, and the
[SaxonC 12 documentation][2] includes instructions for installing and configuring
the product, details of the SaxonC C/C++ API, PHP API, and sample code.
Meanwhile, the main [Saxon 12 documentation][3] contains further information
relevant to SaxonC.

[1]: https://www.saxonica.com/saxon-c/documentation12/index.html#!api/saxon_c_python_api
[2]: https://www.saxonica.com/saxon-c/documentation12/index.html
[3]: https://www.saxonica.com/documentation12/index.html

For further information, see the [SaxonC web page][4]. For bug reporting, 
lists of known bugs, and help forums, please visit the SaxonC bug issue project:
[https://saxonica.plan.io/projects/saxon-c](5).

[4]: https://www.saxonica.com/saxon-c/index.xml
[5]: https://saxonica.plan.io/projects/saxon-c