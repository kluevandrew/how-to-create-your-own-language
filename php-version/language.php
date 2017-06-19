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
require_once __DIR__.'/language/Interpreter/Scope.php';
require_once __DIR__.'/language/Interpreter/Stack.php';
require_once __DIR__.'/language/Interpreter.php';
require_once __DIR__.'/language/Lexer.php';
require_once __DIR__.'/language/Parser.php';
require_once __DIR__.'/language/Token.php';
require_once __DIR__.'/language/Opcode.php';
require_once __DIR__.'/language/FunctionCode.php';

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
echo '</pre>';
echo '<hr>';

$ast = $parser->parse($tokens);

echo '<pre>';
var_export($ast);
echo '</pre>';
echo '<hr>';

$app = $compiler->compile($ast);

echo '<pre>';
var_export($app);
echo '</pre>';
echo '<hr>';


exit($interpreter->run($app));
