#include "stdafx.h"

//C++ process class
class jackpf_c
{
	public:
		bool __construct(void)
		{
			return true;
		}
		void test(void)
		{
			std::fstream file;

			file.open("file.txt", std::fstream::in | std::fstream::out);

			file << "hello";

			file.close();
		}
};

//init process class
jackpf_c *jackpf_c;
//init zend class entry
zend_class_entry *jackpf_ce;

//define functions & methods
ZEND_FUNCTION(jackpf)
{
	char *out;
	int out_len;

	if(zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &out, &out_len) == SUCCESS)
	{
		//php_printf(out);
		PHPWRITE(out, out_len);
		RETURN_TRUE;
	}
	else
	{
		php_error_docref("http://jackpf.co.uk" TSRMLS_CC, E_ERROR, "%s", "Invalid argument.");
		RETURN_FALSE;
	}
}
ZEND_METHOD(jackpf_c, __construct)
{
	jackpf_c->__construct();
	RETURN_TRUE;
}
ZEND_FUNCTION(test)
{
	jackpf_c->test();
}

//define function & method entries
zend_function_entry jackpf_functions[] = {
    ZEND_FE(jackpf, NULL)
	ZEND_FE(test, NULL)
    {NULL, NULL, NULL}
};
function_entry jackpf_c_methods[] = {
	PHP_ME(jackpf_c, __construct, NULL, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
	{NULL, NULL, NULL}
};
ZEND_MINIT_FUNCTION(jackpf_c)
{
    zend_class_entry ce;
    INIT_CLASS_ENTRY(ce, "jackpf_c", jackpf_c_methods);
    jackpf_ce = zend_register_internal_class(&ce TSRMLS_CC);
    return SUCCESS;
}

//register functions & classes with zend...
zend_module_entry jackpf_c_module_entry = {
	STANDARD_MODULE_HEADER,
	"Jackpf's Extension",
	jackpf_functions,
	ZEND_MINIT(jackpf_c),
	NULL, NULL, NULL, NULL,
	NO_VERSION_YET,
	STANDARD_MODULE_PROPERTIES
};
extern "C" {
	ZEND_GET_MODULE(jackpf_c)
}