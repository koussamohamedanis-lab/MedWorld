import type { Message } from './message';

export const createMessage = (
    messageId: number,
    senderId: number,
    receiverId: number,
    cabinetId: number,
    messageText: string,
    consultationId?: number,
    messageDate?: Date,
    status: 'seen' | 'unseen' = 'unseen'
): Message => ({
    id: messageId,
    senderId,
    receiverId,
    cabinetId,
    date: messageDate || new Date(),
    content: {
        message: messageText,
        attachmentId: consultationId
    },
    status
});
