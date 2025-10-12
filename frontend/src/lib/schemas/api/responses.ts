import z from 'zod';

// Base response schema, from ApiResponses trait
const BaseResponseSchema = z.object({
  message: z.string(),
});

// Generic success response wrapper
const successResponseScheme = <T extends z.ZodType>(schema: T) => {
  BaseResponseSchema.extend({
    data: schema,
  });
};
