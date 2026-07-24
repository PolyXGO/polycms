export interface Block {
    id?: string;
    type: string;
    attrs: Record<string, any>;
}

export interface BlockEditorState {
    blocks: Block[];
    selectedBlockId: string | null;
    isDragging: boolean;
}
