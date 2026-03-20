<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Serializers;

use Illuminate\Http\Response as IlluminateResponse;
use Json_Exception;
use Spatie\Response_Cache\Enums\Response_Type;
use Spatie\Response_Cache\Exceptions\Could_Not_Unserialize;
use Symfony\Component\Http_Foundation\Binary_File_Response;
use Symfony\Component\Http_Foundation\Response;
class Json_Serializer implements Serializer
{
    public function serialize(Response $response): string
    {
        $type = match (true) {
            $response instanceof Binary_File_Response => Response_Type::File,
            default => Response_Type::Normal,
        };
        $data = ['type' => $type->value, 'status' => $response->get_status_code(), 'headers' => $response->headers->all(), 'content' => $response instanceof Binary_File_Response ? $response->get_file()->get_pathname() : $response->get_content()];
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
    public function unserialize(string $serialized_response): Response
    {
        try {
            $data = json_decode($serialized_response, true, 512, JSON_THROW_ON_ERROR);
        } catch (Json_Exception) {
            throw Could_Not_Unserialize::serialized_response($serialized_response);
        }
        if (!is_array($data) || !isset($data['type'], $data['status'], $data['headers'], $data['content'])) {
            throw Could_Not_Unserialize::serialized_response($serialized_response);
        }
        $type = Response_Type::from($data['type']);
        $response = match ($type) {
            Response_Type::File => new Binary_File_Response($data['content'], $data['status']),
            Response_Type::Normal => new Illuminate_Response($data['content'], $data['status']),
        };
        foreach ($data['headers'] as $name => $values) {
            $response->headers->set($name, $values);
        }
        return $response;
    }
}