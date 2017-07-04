<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('max_execution_time', 2);

require_once __DIR__.'/language/AST/Node.php';
require_once __DIR__.'/language/AST/ExpressionNode.php';
require_once __DIR__.'/language/AST/StatementNode.php';
require_once __DIR__.'/language/AST/AssignStatement.php';
require_once __DIR__.'/language/AST/ForStatement.php';
require_once __DIR__.'/language/AST/BinaryExpression.php';
require_once __DIR__.'/language/AST/CallExpression.php';
require_once __DIR__.'/language/AST/ExpressionStatement.php';
require_once __DIR__.'/language/AST/IdentifierExpression.php';
require_once __DIR__.'/language/AST/LiteralExpression.php';
require_once __DIR__.'/language/AST/StringExpression.php';
require_once __DIR__.'/language/AST/ParenthesesExpression.php';
require_once __DIR__.'/language/AST/AssignExpression.php';
require_once __DIR__.'/language/AST/IfStatement.php';
require_once __DIR__.'/language/AST/WhileStatement.php';
require_once __DIR__.'/language/AST/FunctionStatement.php';
require_once __DIR__.'/language/AST/ReturnStatement.php';
require_once __DIR__.'/language/AST/RootNode.php';
require_once __DIR__.'/language/Compiler.php';
require_once __DIR__.'/language/Interpreter/Scope.php';
require_once __DIR__.'/language/Interpreter/StdLib.php';
require_once __DIR__.'/language/Interpreter/Stack.php';
require_once __DIR__.'/language/Interpreter/XValue.php';
require_once __DIR__.'/language/Interpreter/NativeFunction.php';
require_once __DIR__.'/language/Interpreter/UserFunction.php';
require_once __DIR__.'/language/Interpreter/ArrayValue.php';
require_once __DIR__.'/language/Interpreter.php';
require_once __DIR__.'/language/Lexer.php';
require_once __DIR__.'/language/Parser.php';
require_once __DIR__.'/language/Token.php';
require_once __DIR__.'/language/Opcode.php';
require_once __DIR__.'/language/FunctionCode.php';
require_once __DIR__.'/language/AST/TypeofExpression.php';
require_once __DIR__.'/language/AST/ArrayExpression.php';
require_once __DIR__.'/language/AST/MemberExpression.php';
require_once __DIR__.'/language/AST/PushExpression.php';

if (isset($_GET)) {
    $file = $_GET['example'] ?? 0;
} else {
    $file = $argv[1] ?? 0;
}

$sourceCode = file_get_contents(__DIR__.'/example'.$file.'.mylang');

$lexer = new Lexer();
$parser = new Parser();
$compiler = new Compiler();
$interpreter = new Interpreter();

echo '<pre>';
echo $sourceCode;
echo '</pre>';
echo '<hr>';

echo '<pre>';
$tokens = $lexer->tokenize($sourceCode);
echo htmlspecialchars($lexer);
echo '</pre>';
echo '<hr>';

echo '<pre>';
$ast = $parser->parse($tokens);
var_export($ast);
echo '</pre>';
echo '<hr>';

echo '<pre>';
$app = $compiler->compile($ast);
var_export($app);
echo '</pre>';
echo '<hr>';

echo '<pre>';
$code = ($interpreter->run($app));
echo '</pre>';
exit($code);