<?php

require_once __DIR__.'/language/AST/Node.php';
require_once __DIR__.'/language/AST/ExpressionNode.php';
require_once __DIR__.'/language/AST/StatementNode.php';
require_once __DIR__.'/language/AST/AssignStatement.php';
require_once __DIR__.'/language/AST/BinaryExpression.php';
require_once __DIR__.'/language/AST/CallExpression.php';
require_once __DIR__.'/language/AST/ExpressionStatement.php';
require_once __DIR__.'/language/AST/IdentifierExpression.php';
require_once __DIR__.'/language/AST/LiteralExpression.php';
require_once __DIR__.'/language/AST/ParenthesesExpression.php';
require_once __DIR__.'/language/AST/RootNode.php';
require_once __DIR__.'/language/Compiler.php';
require_once __DIR__.'/language/Interpreter.php';
require_once __DIR__.'/language/Lexer.php';
require_once __DIR__.'/language/Parser.php';
require_once __DIR__.'/language/Program.php';
require_once __DIR__.'/language/Token.php';



$sourceCode = file_get_contents(__DIR__.'/example.mylang');

$lexer = new Lexer();
$parser = new Parser();
$compiler = new Compiler();
$interpreter = new Interpreter();

error_reporting(E_ALL);
ini_set('display_errors', 'on');
echo '<pre>';
$tokens = $lexer->tokenize($sourceCode);
echo htmlspecialchars($lexer);
echo '</pre><hr><pre>';
$ast = $parser->parse($tokens);
var_export($ast);
echo '</pre>';

//$app = $compiler->compile($ast);
//
//exit($interpreter->run($app));
